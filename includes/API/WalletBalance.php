<?php
declare(strict_types=1);

namespace Snappbox\API;

defined( 'ABSPATH' ) || exit;

/**
 * Wallet Balance API wrapper.
 */
class WalletBalance {
	public function snappb_check_balance() {
		$settings_serialized = \get_option( 'woocommerce_snappbox_shipping_method_settings' );
		$settings            = \maybe_unserialize( $settings_serialized );

		if ( empty( $settings['snappbox_api'] ) ) {
			return array(
				'success' => false,
				'message' => 'API Token is missing.',
			);
		}

		$api_token = $settings['snappbox_api'];
		$url       = 'https://api.snapp-box.com/v1/user/profile';

		$response = \wp_remote_get(
			$url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $api_token,
					'Accept'        => 'application/json',
				),
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

		if ( $status_code === 200 && ! empty( $body['data'] ) ) {
			return array(
				'success' => true,
				'balance' => $body['data']['credits'] ?? 0,
				'name'    => $body['data']['fullname'] ?? '',
			);
		}

		return array(
			'success' => false,
			'message' => $body['message'] ?? 'Unknown error',
		);
	}
}
