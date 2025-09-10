<?php
if (! defined('ABSPATH')) exit;

class WFA_Admin
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_ajax_wfa_resolve_conflict', [$this, 'ajax_resolve_conflict']);
    }

    public function register_menu()
    {
        // Conflict Manager main menu
        add_menu_page(
            'Conflict Manager',
            'Conflict Manager',
            'manage_options',
            'wfa_conflicts',
            [$this, 'page_conflicts'],
            'dashicons-warning',
            55
        );
    }

    public function enqueue_assets($hook)
    {
        // load admin js/css only
        if (strpos($hook, 'wfa_conflicts') !== false) {
            wp_enqueue_script(
                'wfa-admin-js',
                WFA_PLUGIN_URL . 'assets/js/admin-conflicts.js',
                ['jquery'],
                false,
                true
            );

            wp_localize_script('wfa-admin-js', 'wfa_admin', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wfa_resolve_conflict'),
            ]);
            wp_enqueue_style('wfa-admin-css', WFA_PLUGIN_URL . 'assets/css/admin.css');
        }
    }

    public function page_conflicts()
    {
        include WFA_PLUGIN_DIR . 'templates/conflict-manager.php';
    }

    public function ajax_resolve_conflict()
    {
        if (! check_ajax_referer('wfa_resolve_conflict', 'nonce', false)) {
            wp_send_json_error('invalid_nonce', 403);
        }
        if (! current_user_can('manage_options')) {
            wp_send_json_error('no_permission', 403);
        }

        global $wpdb;
        $conf_table = $wpdb->prefix . 'booking_conflicts';

        $conflict_id  = isset($_POST['conflict_id']) ? intval($_POST['conflict_id']) : 0;
        $choice       = isset($_POST['choice']) ? sanitize_text_field($_POST['choice']) : '';
        $manual_value = isset($_POST['manual_value']) ? sanitize_text_field($_POST['manual_value']) : '';
        $note         = isset($_POST['note']) ? sanitize_textarea_field($_POST['note']) : '';

        if (! $conflict_id) {
            wp_send_json_error('invalid_id', 400);
        }

        $conflict = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$conf_table} WHERE id = %d", $conflict_id),
            ARRAY_A
        );

        if (! $conflict) {
            wp_send_json_error('not_found', 404);
        }

        $data = json_decode($conflict['data'], true);
        $resolution = [
            'choice'       => $choice,
            'manual_value' => $manual_value,
            'note'         => $note,
            'resolved_by'  => get_current_user_id(),
            'resolved_at'  => current_time('mysql'),
        ];

        // Save resolution inside data for audit
        $data['resolution'] = $resolution;

        $updated = $wpdb->update(
            $conf_table,
            [
                'is_resolved'    => 1,
                'resolved_by'    => get_current_user_id(),
                'resolution_note' => $note,
                'resolved_at'    => current_time('mysql'),
                'data'           => wp_json_encode($data)
            ],
            ['id' => $conflict_id],
            ['%d', '%d', '%s', '%s', '%s'],
            ['%d']
        );

        if ($updated === false) {
            wp_send_json_error('db_error', 500);
        }

        wp_send_json_success(['message' => 'resolved']);
    }
}

new WFA_Admin();
