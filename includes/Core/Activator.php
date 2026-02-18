<?php
declare(strict_types=1);

namespace Snappbox\Core;

defined( 'ABSPATH' ) || exit;

/**
 * Enterprise Plugin Activator.
 */
class Activator {

	const REDIRECT_OPTION = 'snappbox_qs_do_activation_redirect';

	public static function activate(): void {
		\update_option( self::REDIRECT_OPTION, 'yes' );
		\delete_transient( 'woocommerce_shipping_zones_cache' );
	}

	public static function deactivate(): void {
		\delete_option( self::REDIRECT_OPTION );
	}
}
