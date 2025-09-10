<?php
if (! defined('ABSPATH')) {
    exit;
}

class WFA_Conflict_Handler
{

    private $table_conflicts;

    public function __construct()
    {
        global $wpdb;
        $this->table_conflicts = $wpdb->prefix . 'booking_conflicts';

        // Hook for duplicate cleanup (1 hour later)
        add_action('wfa_cleanup_conflicts', [$this, 'cleanup_old_conflicts']);
    }

    /**
     * Detect conflicts: price mismatch & duplicate booking
     */
    public function check_conflicts($booking)
    {
        // Load APIs
        $api_a = WFA_API_Client::fetch('A');
        $api_b = WFA_API_Client::fetch('B');

        if (! is_wp_error($api_a) && ! is_wp_error($api_b)) {
            foreach ((array) $api_a as $flight_a) {
                foreach ((array) $api_b as $flight_b) {
                    if (
                        strtoupper($flight_a['origin'] . '-' . $flight_a['destination']) === $booking['route'] &&
                        $flight_a['departure_time'] === $booking['departure_time'] &&
                        strtoupper($flight_b['origin'] . '-' . $flight_b['destination']) === $booking['route'] &&
                        $flight_b['departure_time'] === $booking['departure_time']
                    ) {
                        $price_diff = abs($flight_a['price'] - $flight_b['price']);
                        $threshold  = max($flight_a['price'], $flight_b['price']) * 0.10;

                        if ($price_diff > $threshold) {
                            $this->log_conflict($booking['id'], 'price_mismatch', [
                                'api_a' => $flight_a,
                                'api_b' => $flight_b,
                            ]);
                        }
                    }
                }
            }
        }

        // Duplicate booking: same email within 1 hour
        global $wpdb;
        $table = $wpdb->prefix . 'flight_bookings';

        $recent = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table 
             WHERE email = %s 
             AND created_at >= (NOW() - INTERVAL 1 HOUR) 
             AND id != %d",
            $booking['email'],
            $booking['id']
        ));

        if ($recent > 0) {
            $this->log_conflict($booking['id'], 'duplicate_booking', $booking);
        }
    }

    /**
     * Log a conflict + send mail to admin
     */
    private function log_conflict($booking_id, $type, $conflict_data)
    {
        global $wpdb;

        $wpdb->insert(
            $this->table_conflicts,
            [
                'booking_id'   => $booking_id,
                'conflict_type' => sanitize_text_field($type),
                'data'         => wp_json_encode($conflict_data),
                'is_resolved'  => 0,
                'created_at'   => current_time('mysql'),
            ]
        );

        // Send email
        $admin_email = get_option('admin_email');
        $subject     = "Flight Booking Conflict Logged (#{$booking_id})";
        $message     = "A conflict has been detected:\n\nType: {$type}\n\nDetails:\n" . 
            print_r($conflict_data, true);

        wp_mail($admin_email, $subject, $message);

        // Schedule cleanup (auto-cancel after 1 hour)
        if (! wp_next_scheduled('wfa_cleanup_conflicts')) {
            wp_schedule_single_event(time() + HOUR_IN_SECONDS, 'wfa_cleanup_conflicts');
        }
    }

    /**
     * Cleanup old duplicate conflicts (after 1 hour)
     */
    public function cleanup_old_conflicts()
    {
        global $wpdb;

        $wpdb->query(
            "DELETE FROM {$this->table_conflicts} 
             WHERE conflict_type = 'duplicate_booking' 
             AND created_at < (NOW() - INTERVAL 1 HOUR)"
        );
    }
}
