<?php
declare(strict_types=1);

namespace Snappbox\API;

defined('ABSPATH') || exit;

/**
 * Reverse Geocoder service for SnappMaps.
 */
class ReverseGeocoder {

    private $base_url  = 'https://api.teh-1.snappmaps.ir/reverse/v1';
    private $auth_token;
    private $smapp_key;

    public function __construct() {
        $this->auth_token = \defined('SNAPPBOX_MAP_TOKEN') ? \SNAPPBOX_MAP_TOKEN : '';
        $this->smapp_key  = \defined('SNAPPBOX_SMAPP_KEY') ? \SNAPPBOX_SMAPP_KEY : '';
    }

    public function get_address($lat, $lng, $language) {
        $url = \add_query_arg([
            'lat'      => $lat,
            'lon'      => $lng,
            'language' => $language,
        ], $this->base_url);

        $response = \wp_remote_get($url, [
            'headers' => [
                'Accept'        => 'application/json',
                'Authorization' => $this->auth_token,
                'X-Smapp-Key'   => $this->smapp_key,
                'User-Agent'    => 'SnappBoxWoo/1.0',
            ]
        ]);

        if (\is_wp_error($response)) {
            return new \WP_Error('snappmaps_error', 'Failed to communicate with SnappMaps API.');
        }

        $body = \wp_remote_retrieve_body($response);
        return \json_decode($body, true);
    }
}
