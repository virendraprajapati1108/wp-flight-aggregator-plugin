<?php

if (! defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'wfa-frontend-css',
        WFA_PLUGIN_URL . 'assets/css/frontend.css',
        [],
        '1.0'
    );
});
