<?php
if (! defined('ABSPATH')) exit;

class WFA_Bookings
{

    public static function init()
    {
        add_action('admin_post_nopriv_wfa_save_booking', [__CLASS__, 'handle_booking']);
        add_action('admin_post_wfa_save_booking', [__CLASS__, 'handle_booking']);
    }

    public static function handle_booking()
    {
        if (! isset($_POST['wfa_book_nonce']) || ! wp_verify_nonce($_POST['wfa_book_nonce'], 'wfa_book')) {
            wp_die('Invalid request (nonce).');
        }

        global $wpdb;
        $prefix = $wpdb->prefix;
        $table  = $prefix . 'flight_bookings';

        // Flight data
        $flight_json = isset($_POST['flight_json']) ? wp_unslash($_POST['flight_json']) : '';
        $flight      = json_decode($flight_json, true);
        if (! $flight || ! is_array($flight)) {
            wp_die('Invalid flight data.');
        }

        // Passenger info
        $name       = sanitize_text_field($_POST['passenger_name'] ?? '');
        $email      = sanitize_email($_POST['email'] ?? '');
        $mobile     = sanitize_text_field($_POST['mobile'] ?? '');
        $seat_count = absint($_POST['seat_count'] ?? 1);

        // Flight info
        $flight_id      = sanitize_text_field($flight['flight_id'] ?? '');
        $source         = sanitize_text_field($flight['source'] ?? '');
        $origin         = sanitize_text_field($flight['origin'] ?? '');
        $destination    = sanitize_text_field($flight['destination'] ?? '');
        $route          = strtoupper($origin . '-' . $destination);
        $departure_time = sanitize_text_field($flight['departure_time'] ?? '');
        $price          = floatval($flight['price'] ?? 0);

        // Insert booking
        $wpdb->insert(
            $table,
            [
                'flight_id'      => $flight_id,
                'source'         => $source,
                'route'          => $route,
                'departure_time' => $departure_time,
                'price'          => $price,
                'passenger_name' => $name,
                'email'          => $email,
                'mobile'         => $mobile,
                'seat_count'     => $seat_count,
            ],
            ['%s', '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%d']
        );

        $booking_id = $wpdb->insert_id;

        // Run conflict detection
        $conflict_handler = new WFA_Conflict_Handler();
        $conflict_handler->check_conflicts([
            'id'             => $booking_id,
            'flight_id'      => $flight_id,
            'source'         => $source,
            'route'          => $route,
            'departure_time' => $departure_time,
            'price'          => $price,
            'email'          => $email,
        ]);

        // Redirect back with confirmation
        wp_safe_redirect(add_query_arg('wfa_booked', '1', wp_get_referer() ?: home_url()));
        exit;
    }
}

WFA_Bookings::init();
