<?php
declare(strict_types=1);

namespace Snappbox\API;

defined( 'ABSPATH' ) || exit;

/**
 * Autonomous Live Tracking Service for SnappBox Elite.
 */
class LiveTracking {
	/**
	 * Get the live location of a driver for a specific order.
	 *
	 * @param string $snappbox_order_id The SnappBox Order ID.
	 * @return array{success:bool, latitude:float|null, longitude:float|null, message?:string}
	 */
	public function get_location( string $snappbox_order_id ): array {
		$cache_key = 'snappb_tracking_' . $snappbox_order_id;
		$cached    = \get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$settings_serialized = \get_option( 'woocommerce_snappbox_shipping_method_settings' );
		$settings            = \maybe_unserialize( $settings_serialized );

		if ( empty( $settings['snappbox_api'] ) ) {
			return array(
				'success'   => false,
				'message'   => 'API Token is missing.',
				'latitude'  => null,
				'longitude' => null,
			);
		}

		$api_token = $settings['snappbox_api'];
		$url       = 'https://api.snapp-box.com/v1/orders/' . $snappbox_order_id . '/current-location';

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
				'success'   => false,
				'message'   => $response->get_error_message(),
				'latitude'  => null,
				'longitude' => null,
			);
		}

		$status_code = \wp_remote_retrieve_response_code( $response );
		$body        = \json_decode( \wp_remote_retrieve_body( $response ), true );

		if ( 200 === $status_code && ! empty( $body ) ) {
			$result = array(
				'success'   => true,
				'latitude'  => $body['latitude'] ?? null,
				'longitude' => $body['longitude'] ?? null,
			);
			\set_transient( $cache_key, $result, 30 ); // Tight cache for live data
			return $result;
		}

		return array(
			'success'   => false,
			'message'   => $body['message'] ?? 'Unknown tracking error',
			'latitude'  => null,
			'longitude' => null,
		);
	}
}
