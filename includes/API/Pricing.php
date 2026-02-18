<?php
declare(strict_types=1);

namespace Snappbox\API;

defined('ABSPATH') || exit;

/**
 * Pricing API wrapper.
 */
class Pricing
{
    public function snappb_get_pricing($data)
    {
        $settings_serialized = \get_option('woocommerce_snappbox_shipping_method_settings');
        $settings = \maybe_unserialize($settings_serialized);

        if (empty($settings['snappbox_api'])) {
            return ['success' => false, 'message' => 'API Token is missing.'];
        }

        $api_token = $settings['snappbox_api'];
        $url       = 'https://api.snapp-box.com/v2/pricing';

        $response = \wp_remote_post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_token,
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ],
            'body'    => \json_encode($data),
        ]);

        if (\is_wp_error($response)) {
            return ['success' => false, 'message' => $response->get_error_message()];
        }

        $status_code = \wp_remote_retrieve_response_code($response);
        $body        = \json_decode(\wp_remote_retrieve_body($response), true);

        if ($status_code === 200 && !empty($body['data'])) {
            return [
                'success' => true,
                'price'   => $body['data']['price'] ?? 0,
            ];
        }

        return ['success' => false, 'message' => $body['message'] ?? 'Unknown error'];
    }
}
