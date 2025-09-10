<?php

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}

function wfa_log_error($message)
{
    $logdir = WP_CONTENT_DIR . '/logs';
    if (! file_exists($logdir)) {
        @mkdir($logdir, 0755, true);
    }
    @error_log(date('c') . " | WFA | " . $message . "\n", 3, $logdir . '/flight-errors.log');
}