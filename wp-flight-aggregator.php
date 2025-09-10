<?php
/*
Plugin Name: WP Flight Aggregator
Description: Basic flight aggregator: pulls two external APIs, allows booking, detects conflicts and provides admin conflict resolver.
Version: 1.0
Author: Your Name
*/

if ( ! defined('ABSPATH')) exit;

define('WFA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WFA_PLUGIN_URL', plugin_dir_url(__FILE__));

// Includes
require_once WFA_PLUGIN_DIR . 'includes/helpers.php';
require_once WFA_PLUGIN_DIR . 'includes/class-shortcode.php';
require_once WFA_PLUGIN_DIR . 'includes/class-bookings.php';
require_once WFA_PLUGIN_DIR . 'includes/class-admin.php';
require_once WFA_PLUGIN_DIR . 'includes/class-api-client.php';
require_once WFA_PLUGIN_DIR . 'includes/class-conflict-handler.php';
require_once WFA_PLUGIN_DIR . 'includes/class-cron.php';
require_once WFA_PLUGIN_DIR . 'includes/class-flight-api.php';
require_once WFA_PLUGIN_DIR . 'includes/class-install.php';

// Activation / Deactivation hooks
register_activation_hook(__FILE__, ['WFA_Install', 'activate']);
register_deactivation_hook(__FILE__, ['WFA_Install', 'deactivate']);

// Shortcode
add_action('init', function () {
    add_shortcode('flight_search', ['WFA_Shortcode', 'render']);
});
