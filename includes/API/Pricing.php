<?php
declare(strict_types=1);

namespace Snappbox\API;

defined( 'ABSPATH' ) || exit;

/**
 * Pricing API wrapper.
 */
class Pricing {
	public function snappb_get_pricing( $data ) {
		$cache_key = 'snappb_pricing_' . md5( json_encode( $data ) );
		$cached    = \get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$settings_serialized = \get_option( 'woocommerce_snappbox_shipping_method_settings' );
		$settings            = \maybe_unserialize( $settings_serialized );

		if ( empty( $settings['snappbox_api'] ) ) {
			return array(
				'success' => false,
				'message' => 'API Token is missing.',
			);
		}

		$api_token = $settings['snappbox_api'];
		$url       = 'https://api.snapp-box.com/v2/pricing';

		$response = \wp_remote_post(
			$url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_token,
					'Accept'        => 'application/json',
					'Content-Type'  => 'application/json',
				),
				'body'    => \json_encode( $data ),
			)
		);

		if ( \is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => $response->get_error_message(),
			);
		}

		$status_code = \wp_remote_retrieve_response_code( $response );
		$body        = \json_decode( \wp_remote_retrieve_body( $response ), true );

		if ( 200 === $status_code && ! empty( $body['data'] ) ) {
			$result = array(
				'success' => true,
				'price'   => $body['data']['price'] ?? 0,
			);
			\set_transient( $cache_key, $result, 5 * MINUTE_IN_SECONDS );
			return $result;
		}

		return array(
			'success' => false,
			'message' => $body['message'] ?? 'Unknown error',
		);
	}
}
