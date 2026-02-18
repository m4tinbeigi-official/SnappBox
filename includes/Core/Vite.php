<?php
declare(strict_types=1);
declare(strict_types=1);

namespace Snappbox\Core;

/**
 * Enterprise Vite bridge for WordPress.
 * Handles dynamic asset loading from Vite manifest.
 */
class Vite {

	private const DIST_DIR   = 'assets/dist/';
	private const SERVER_URL = 'http://localhost:5173/';

	/**
	 * Enqueue a Vite asset.
	 */
	public static function enqueue( string $entry, array $deps = array(), bool $is_admin = true ): void {
		if ( self::is_dev() ) {
			self::enqueue_dev( $entry, $deps );
		} else {
			self::enqueue_prod( $entry, $deps );
		}
	}

	/**
	 * Check if we are in development mode.
	 */
	private static function is_dev(): bool {
		return defined( 'SNAPPBOX_FRONTEND_DEV' ) && SNAPPBOX_FRONTEND_DEV;
	}

	/**
	 * Enqueue assets for development (HMR).
	 */
	private static function enqueue_dev( string $entry, array $deps ): void {
		// Enqueue Vite client
		\wp_enqueue_script( 'vite-client', self::SERVER_URL . '@vite/client', array(), null, true );

		// Enqueue entry point
		\wp_enqueue_script( 'snappbox-' . $entry, self::SERVER_URL . 'src/' . $entry, array( 'vite-client' ), null, true );

		// Fix for React HMR
		\add_action( 'wp_head', array( self::class, 'vite_dev_header_scripts' ) );
		\add_action( 'admin_head', array( self::class, 'vite_dev_header_scripts' ) );
	}

	public static function vite_dev_header_scripts(): void {
		?>
		<script type="module">
			import RefreshRuntime from "<?php echo self::SERVER_URL; ?>@react-refresh"
			RefreshRuntime.injectIntoGlobalHook(window)
			window.$RefreshReg$ = () => {}
			window.$RefreshSig$ = () => (type) => type
			window.__vite_plugin_react_preamble_installed__ = true
		</script>
		<?php
	}

	/**
	 * Enqueue assets for production (Manifest).
	 */
	private static function enqueue_prod( string $entry, array $deps ): void {
		$manifest = self::get_manifest();
		if ( ! $manifest || ! isset( $manifest[ $entry ] ) ) {
			return;
		}

		$asset = $manifest[ $entry ];

		// Enqueue CSS
		if ( isset( $asset['css'] ) ) {
			foreach ( $asset['css'] as $css_file ) {
				\wp_enqueue_style( 'snappbox-' . md5( $css_file ), SNAPPBOX_URL . self::DIST_DIR . $css_file );
			}
		}

		// Enqueue JS
		\wp_enqueue_script( 'snappbox-' . $entry, SNAPPBOX_URL . self::DIST_DIR . $asset['file'], $deps, null, true );
	}

	private static function get_manifest(): ?array {
		$manifest_path = SNAPPBOX_DIR . self::DIST_DIR . '.vite/manifest.json';
		if ( ! file_exists( $manifest_path ) ) {
			return null;
		}
		return json_decode( file_get_contents( $manifest_path ), true );
	}
}
