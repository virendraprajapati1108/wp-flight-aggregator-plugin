<?php
if (! defined('ABSPATH')) exit;

class WFA_Shortcode
{

    public static function render()
    {
        ob_start();
        include WFA_PLUGIN_DIR . 'templates/search-form.php';

        // If search submitted via GET, fetch and display flights
        $origin = isset($_GET['wfa_origin']) ? sanitize_text_field($_GET['wfa_origin']) : '';
        $destination = isset($_GET['wfa_destination']) ? sanitize_text_field($_GET['wfa_destination']) : '';
        $date = isset($_GET['wfa_date']) ? sanitize_text_field($_GET['wfa_date']) : '';

        if ($origin && $destination && $date) {

            // fetch both APIs
            $a = WFA_API_Client::fetch('A');
            $b = WFA_API_Client::fetch('B');

            $flights = [];

            if (! is_wp_error($a)) {
                foreach ((array) $a as $f) {
                    $f['source'] = 'A';
                    if (self::filter_match($f, $origin, $destination, $date)) $flights[] = $f;
                }
            }

            if (! is_wp_error($b)) {
                foreach ((array) $b as $f) {
                    $f['source'] = 'B';
                    if (self::filter_match($f, $origin, $destination, $date)) $flights[] = $f;
                }
            }

            // Optionally sort by price
            usort($flights, function ($x, $y) {
                $px = isset($x['price']) ? floatval($x['price']) : PHP_INT_MAX;
                $py = isset($y['price']) ? floatval($y['price']) : PHP_INT_MAX;
                return $px <=> $py;
            });

            include WFA_PLUGIN_DIR . 'templates/flights-table.php';
        }

        return ob_get_clean();
    }

    private static function filter_match($f, $origin, $destination, $date)
    {
        if (empty($f['origin']) || empty($f['destination']) || empty($f['departure_time'])) return false;

        if (strtoupper($f['origin']) !== strtoupper($origin)) return false;
        
        if (strtoupper($f['destination']) !== strtoupper($destination)) return false;

        // Compare date part only
        $fdate = date('Y-m-d', strtotime($f['departure_time']));

        return $fdate === $date;
    }
}
