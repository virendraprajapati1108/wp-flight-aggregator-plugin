<?php
// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit;
}

class WFA_Flight_API
{

    // Fetch flights from both sources and merge
    public function get_flights($origin, $destination, $date)
    {
        $flights_a = $this->fetch_from_source(WFA_API_A_URL, 'Source A');
        $flights_b = $this->fetch_from_source(WFA_API_B_URL, 'Source B');

        $all_flights = array_merge($flights_a, $flights_b);

        // Optional: filter by params (hardcoded/simple for demo)
        $filtered = array_filter(
            $all_flights,
            function ($flight) use ($origin, $destination, $date) {
                return $flight['origin'] === $origin
                    && $flight['destination'] === $destination
                    && $flight['date'] === $date;
            }
        );

        return array_values($filtered);
    }

    // Fetch from a single API source
    private function fetch_from_source($url, $source)
    {
        $response = wp_remote_get($url, ['timeout' => 15]);

        if (is_wp_error($response)) {
            wfa_log_error("{$source} | " . $response->get_error_message());
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (! $data) {
            wfa_log_error("{$source} | Invalid JSON response");
            return [];
        }

        // Attach source name to each flight
        foreach ($data as &$flight) {
            $flight['source_name'] = $source;
        }

        // foreach ($data as $key => $flight) {
        //     $data[$key]['source_name'] = $source;
        // }
        
        return $data;
    }
}
