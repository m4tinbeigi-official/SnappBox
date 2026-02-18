<?php
declare(strict_types=1);

namespace Snappbox\API;

defined('ABSPATH') || exit;

/**
 * Order Cancellation service for SnappBox.
 */
class CancelOrder {
    private $api_url;
    private $api_token;

    public function __construct() {
        global $snappb_api_base_url;
        $this->api_url   = $snappb_api_base_url . '/v1/orders/';
        $this->api_token = \defined('SNAPPBOX_API_TOKEN') ? \SNAPPBOX_API_TOKEN : '';
    }

    public function cancel(string $order_id) {
        $url = $this->api_url . $order_id;
        $response = \wp_remote_request($url, [
            'method'  => 'DELETE',
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => $this->api_token,
            ],
        ]);

        if (\is_wp_error($response)) {
            return ['success' => false, 'message' => $response->get_error_message()];
        }

        return \json_decode(\wp_remote_retrieve_body($response), true);
    }
}
