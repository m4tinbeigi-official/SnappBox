<?php
declare(strict_types=1);

namespace Snappbox\API;

defined('ABSPATH') || exit;

/**
 * NearBy API wrapper.
 */
class NearBy
{
    public function snappb_check_nearby($lat, $lng)
    {
        $settings_serialized = \get_option('woocommerce_snappbox_shipping_method_settings');
        $settings = \maybe_unserialize($settings_serialized);

        if (empty($settings['snappbox_api'])) {
            return ['success' => false, 'message' => 'API Token is missing.'];
        }

        $api_token = $settings['snappbox_api'];
        $url       = "https://api.snapp-box.com/v1/nearby?lat={$lat}&lng={$lng}";

        $response = \wp_remote_get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_token,
                'Accept'        => 'application/json',
            ],
        ]);

        if (\is_wp_error($response)) {
            return ['success' => false, 'message' => $response->get_error_message()];
        }

        $status_code = \wp_remote_retrieve_response_code($response);
        $body        = \json_decode(\wp_remote_retrieve_body($response), true);

        if ($status_code === 200) {
            return [
                'success' => true,
                'data'    => $body['data'] ?? [],
            ];
        }

        return ['success' => false, 'message' => $body['message'] ?? 'Unknown error'];
    }
}
