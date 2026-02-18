<?php
declare(strict_types=1);

namespace Snappbox\API;

defined( 'ABSPATH' ) || exit;

/**
 * Cities API wrapper.
 */
class Cities {
	public function snappb_check_cities() {
		// Logic remains same, only namespace changed
		$url      = 'https://api.teh-1.snappmaps.ir/shipping/v1/cities';
		$response = \wp_remote_get( $url );
		if ( \is_wp_error( $response ) ) {
			return array();
		}
		return \json_decode( \wp_remote_retrieve_body( $response ), true );
	}
}
