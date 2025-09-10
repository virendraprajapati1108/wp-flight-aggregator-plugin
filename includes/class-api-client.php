<?php
if (! defined('ABSPATH')) exit;

// Set Mocki endpoints here
if (! defined('WFA_API_A_URL')) define('WFA_API_A_URL', 'https://mocki.io/v1/eba83e78-1f96-4614-92b3-bee1b7abb30a');
if (! defined('WFA_API_B_URL')) define('WFA_API_B_URL', 'https://mocki.io/v1/1c3811a6-727e-4094-95f2-1802476b9cdf');

class WFA_API_Client
{
    public static function fetch($which)
    {
        $url = $which === 'A' ? WFA_API_A_URL : WFA_API_B_URL;

        if (empty($url)) {
            return new WP_Error('no_endpoint', 'No endpoint configured for source ' . $which);
        }

        $resp = wp_remote_get($url, array(
            'timeout' => 12,
            'headers' => array('Accept' => 'application/json')
        ));

        if (is_wp_error($resp)) {
            wfa_log_error("wp_remote_get error for {$which}: " . $resp->get_error_message());
            return $resp;
        }

        $code = wp_remote_retrieve_response_code($resp);
        $body = wp_remote_retrieve_body($resp);

        if ($code !== 200) {
            wfa_log_error("HTTP {$code} from {$which} - " . substr($body, 0, 300));
            return new WP_Error('http_error', "HTTP {$code}");
        }

        $data = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            wfa_log_error("JSON decode error for {$which}: " . json_last_error_msg());
            return new WP_Error('json_error', json_last_error_msg());
        }

        // Expect an array of flights; if object, try 'flights' key
        if (is_array($data)) return $data;
        if (isset($data['flights']) && is_array($data['flights'])) return $data['flights'];
        
        return new WP_Error('bad_format', 'Unexpected API format');
    }
}
