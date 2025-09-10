<?php
// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}

class WFA_Install
{
    public static function activate()
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();

        $bookings = $wpdb->prefix . 'flight_bookings';
        $conflicts = $wpdb->prefix . 'booking_conflicts';

        $sql1 = "CREATE TABLE {$bookings} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            flight_id VARCHAR(100) NOT NULL,
            source VARCHAR(20) NOT NULL,
            route VARCHAR(100) NOT NULL,
            departure_time DATETIME NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            passenger_name VARCHAR(200) NOT NULL,
            email VARCHAR(255) NOT NULL,
            mobile VARCHAR(30) NOT NULL,
            seat_count INT UNSIGNED NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY (email),
            KEY (created_at)
        ) {$charset_collate};";

        $sql2 = "CREATE TABLE {$conflicts} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            booking_id BIGINT UNSIGNED NOT NULL,
            conflict_type VARCHAR(50) NOT NULL,
            data LONGTEXT NOT NULL,
            is_resolved TINYINT(1) NOT NULL DEFAULT 0,
            resolved_by BIGINT UNSIGNED NULL,
            resolution_note TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            resolved_at DATETIME NULL,
            PRIMARY KEY (id),
            KEY (booking_id),
            KEY (is_resolved)
        ) {$charset_collate};";

        dbDelta($sql1);
        dbDelta($sql2);

        // Schedule cron job for duplicate cleanup
        if (! wp_next_scheduled('wfa_cleanup_duplicates')) {
            wp_schedule_event(time(), 'hourly', 'wfa_cleanup_duplicates');
        }
    }

    public static function deactivate()
    {
        // Clear scheduled cron task
        wp_clear_scheduled_hook('wfa_cleanup_duplicates');
    }
}
