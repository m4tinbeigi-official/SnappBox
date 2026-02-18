<?php
declare(strict_types=1);

namespace Snappbox\API;

defined( 'ABSPATH' ) || exit;

/**
 * Order Status Check service for SnappBox.
 */
class StatusCheck {
	private $apiUrl;
	private $headers;

	public function __construct() {
		global $snappb_api_base_url;
		$this->apiUrl  = $snappb_api_base_url . '/v1/orders/';
		$this->headers = array(
			'Content-Type'  => 'application/json',
			'Authorization' => \defined( 'SNAPPBOX_API_TOKEN' ) ? \SNAPPBOX_API_TOKEN : '',
		);
	}

	public function get_status( string $orderID ) {
		$url      = $this->apiUrl . $orderID;
		$response = \wp_remote_get( $url, array( 'headers' => $this->headers ) );

		if ( \is_wp_error( $response ) ) {
			throw new \Exception( 'Request error: ' . \esc_html( $response->get_error_message() ) );
		}

		return \json_decode( \wp_remote_retrieve_body( $response ), true );
	}
}
