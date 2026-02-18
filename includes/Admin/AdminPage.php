<?php
declare(strict_types=1);

namespace Snappbox\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Professional Settings Page for SnappBox.
 */
class AdminPage {

	public function boot(): void {
		\add_action( 'admin_menu', array( $this, 'add_admin_page' ) );
		\add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_admin_page(): void {
		\add_submenu_page(
			'woocommerce', // Moving to WooCommerce menu for better context
			'SnappBox Elite',
			'SnappBox',
			'manage_woocommerce',
			'snappbox-settings',
			array( $this, 'render_page' )
		);
	}

	public function render_page(): void {
		?>
		<div class="wrap snappbox-modern-ui">
			<h1><?php \esc_html_e( 'SnappBox Settings', 'snappbox' ); ?></h1>
			<div class="elite-card">
				<form method="post" action="options.php">
					<?php
					\settings_fields( 'snappbox-settings' );
					\do_settings_sections( 'snappbox-settings' );
					\submit_button();
					?>
				</form>
			</div>
		</div>
		<?php
	}

	public function register_settings(): void {
		$configs = $this->get_settings_config();
		
		\add_settings_section( 'snappbox-main', \__( 'General Settings', 'snappbox' ), null, 'snappbox-settings' );

		foreach ( $configs as $id => $config ) {
			\register_setting( 'snappbox-settings', $id, array( 'sanitize_callback' => $config['sanitize'] ) );
			\add_settings_field( 
				$id, 
				$config['label'], 
				function() use ($id, $config) { $this->render_field($id, $config); }, 
				'snappbox-settings', 
				'snappbox-main' 
			);
		}
	}

	private function get_settings_config(): array {
		return array(
			'snappbox_api' => array(
				'label'    => \__( 'API Token', 'snappbox' ),
				'sanitize' => 'sanitize_text_field',
				'type'     => 'text',
				'default'  => ''
			),
			// AUTO_APPEND_HERE (Reserved for Elite Automation)
		);
	}

	private function render_field( string $id, array $config ): void {
		$value = \get_option( $id, $config['default'] );
		echo '<input type="' . \esc_attr( $config['type'] ) . '" name="' . \esc_attr( $id ) . '" value="' . \esc_attr( $value ) . '" class="regular-text" />';
	}
}
