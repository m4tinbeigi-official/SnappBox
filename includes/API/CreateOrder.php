<?php
declare(strict_types=1);

namespace Snappbox\API;

defined( 'ABSPATH' ) || exit;

/**
 * Create Order API wrapper.
 */
class CreateOrder {
	public function snappb_create_order( $data ) {
		$settings_serialized = \get_option( 'woocommerce_snappbox_shipping_method_settings' );
		$settings            = \maybe_unserialize( $settings_serialized );

		if ( empty( $settings['snappbox_api'] ) ) {
			return array(
				'success' => false,
				'message' => 'API Token is missing.',
			);
		}

		$api_token = $settings['snappbox_api'];
		$url       = 'https://api.snapp-box.com/v2/orders';

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

		if ( $status_code === 200 || $status_code === 201 ) {
			return array(
				'success' => true,
				'data'    => $body['data'] ?? array(),
			);
		}

		return array(
			'success' => false,
			'message' => $body['message'] ?? 'Unknown error',
		);
	}
}
