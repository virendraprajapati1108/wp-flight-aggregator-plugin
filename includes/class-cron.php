<?php
// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}

class WFA_Cron
{

    public function __construct()
    {
        // Hook our cleanup task
        add_action('wfa_cleanup_duplicates', [$this, 'cleanup_duplicates']);
    }

    /**
     * Cleanup duplicate bookings made within 1 hour by same email
     */
    public function cleanup_duplicates()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'flight_bookings';

        // Find emails with more than 1 booking in the past hour
        $results = $wpdb->get_results("
            SELECT email, COUNT(*) as cnt
            FROM $table
            WHERE created_at >= (NOW() - INTERVAL 1 HOUR)
            GROUP BY email
            HAVING cnt > 1
        ");

        if (empty($results)) {
            return;
        }

        foreach ($results as $dup) {
            // Get all bookings for this email in the last hour
            $to_delete = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id FROM $table WHERE email=%s ORDER BY created_at ASC",
                    $dup->email
                )
            );

            // Keep the first booking, delete the rest
            array_shift($to_delete);

            foreach ($to_delete as $row) {
                $wpdb->delete($table, ['id' => $row->id]);
            }
        }
    }
}

// Initialize cron handler
new WFA_Cron();
