<?php
declare(strict_types=1);

namespace Snappbox\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Setup Wizard for SnappBox.
 */
class SetupWizard {
	private $page_slug     = 'snappbox-quick-setup';
	private $nonce_action  = 'snappbox_qs_save';
	private $wc_option_key = 'woocommerce_snappbox_shipping_method_settings';

	/**
	 * Boot the service (called by DI Container).
	 */
	public function boot(): void {
		\add_action( 'admin_init', array( $this, 'snappb_maybe_redirect_after_activation' ) );
		\add_action( 'admin_menu', array( $this, 'snappb_add_menu' ) );
		\add_action( 'admin_enqueue_scripts', array( $this, 'snappb_enqueue_assets' ) );
		\add_action( 'admin_post_snappbox_qs_save', array( $this, 'snappb_handle_save' ) );
	}

	/**
	 * Activation hook handler.
	 */
	public static function on_activate(): void {
		\add_option( 'snappbox_qs_do_activation_redirect', 'yes' );
	}

	public function snappb_maybe_redirect_after_activation(): void {
		if ( \get_option( 'snappbox_qs_do_activation_redirect' ) === 'yes' ) {
			\delete_option( 'snappbox_qs_do_activation_redirect' );

			$activate_multi = isset( $_GET['activate-multi'] ) ? \sanitize_text_field( \wp_unslash( $_GET['activate-multi'] ) ) : '';

			if ( $activate_multi === '' && \current_user_can( 'manage_woocommerce' ) ) {
				\wp_safe_redirect( $this->snappb_url_for_step( 1 ) );
				exit;
			}
		}
	}

	public function snappb_add_menu(): void {
		\add_submenu_page(
			'woocommerce',
			\__( 'SnappBox Quick Setup', 'snappbox' ),
			\__( 'SnappBox Quick Setup', 'snappbox' ),
			'manage_woocommerce',
			$this->page_slug,
			array( $this, 'snappb_render_page' )
		);
	}

	public function snappb_enqueue_assets( $hook ): void {
		if ( $hook !== 'woocommerce_page_' . $this->page_slug ) {
			return;
		}

		// Modern Asset Enqueueing via Vite Bridge
		\Snappbox\Core\Vite::enqueue( 'setup-wizard/main.tsx' );
	}

	public function snappb_render_page(): void {
		if ( ! \current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		// Professional React Root
		echo '<div id="sb-wizard-root" class="snappbox-modern-ui"></div>';
	}

	private function snappb_current_step(): int {
		$raw = isset( $_GET['step'] ) ? \sanitize_text_field( \wp_unslash( $_GET['step'] ) ) : '1';
		$s   = (int) $raw;
		return \max( 1, \min( 4, $s ) );
	}

	private function snappb_url_for_step( $n ): string {
		return \add_query_arg(
			array(
				'page' => $this->page_slug,
				'step' => (int) $n,
			),
			\admin_url( 'admin.php' )
		);
	}

	private function snappb_render_stepper( $step ): string {
		$titles = array(
			1 => \esc_html_x( 'API Token', 'Wizard step title', 'snappbox' ),
			2 => \esc_html_x( 'Map Setup', 'Wizard step title', 'snappbox' ),
			3 => \esc_html_x( 'Store Info', 'Wizard step title', 'snappbox' ),
			4 => \esc_html_x( 'Other Info', 'Wizard step title', 'snappbox' ),
		);

		\ob_start();
		?>
		<div class="sbqs-stepper" role="navigation" aria-label="<?php echo \esc_attr_x( 'Wizard steps', 'ARIA', 'snappbox' ); ?>">
			<?php
			for ( $i = 1; $i <= 4; $i++ ) :
				$active  = ( $i <= $step ) ? ' active' : '';
				$current = ( $i === $step ) ? ' current' : '';
				?>
				<div class="sbqs-step<?php echo \esc_attr( $active . $current ); ?>">
					<div class="sbqs-title"><?php echo \esc_html( $titles[ $i ] ); ?></div>
					<?php if ( $i < $step ) : ?>
						<a class="sbqs-dot"
							href="<?php echo \esc_url( $this->snappb_url_for_step( $i ) ); ?>"
							aria-label="
							<?php
							echo \esc_attr(
								\sprintf(
									\esc_html( 'Go to step %d', 'snappbox' ),
									$i
								)
							);
							?>
										">
							<?php echo \esc_html( (string) $i ); ?>
						</a>
					<?php else : ?>
						<span class="sbqs-dot" aria-current="step"><?php echo \esc_html( (string) $i ); ?></span>
					<?php endif; ?>
				</div>
			<?php endfor; ?>
		</div>
		<?php
		return (string) \ob_get_clean();
	}

	private function snappb_render_form_open( $step ): void {
		?>
		<form method="post" action="<?php echo \esc_url( \admin_url( 'admin-post.php' ) ); ?>" class="sbqs-form">
			<input type="hidden" name="action" value="snappbox_qs_save" />
			<input type="hidden" name="step" value="<?php echo \esc_attr( (string) (int) $step ); ?>" />
			<?php \wp_nonce_field( $this->nonce_action, '_snappbox_qs_nonce' ); ?>
		<?php
	}

	private function snappb_render_form_close( $step, $is_last = false ): void {
		?>
			<div class="sbqs-actions">
				<?php if ( $step > 1 ) : ?>
					<a class="button button-secondary sbqs-btn"
						href="<?php echo \esc_url( $this->snappb_url_for_step( $step - 1 ) ); ?>">
						<?php echo \esc_html_x( 'Back', 'Button', 'snappbox' ); ?>
					</a>
				<?php endif; ?>

				<button
					type="submit"
					class="button button-primary sbqs-btn"
					<?php
					if ( 2 === $step ) {
						?>
						disabled="disabled" <?php } ?>
					<?php
					if ( 1 === $step ) {
						?>
						onclick="ym(105087875,'reachGoal',' step-1'); setTimeout(() => { this.form.submit(); }, 150); return false;" <?php } ?>
					<?php
					if ( 2 === $step ) {
						?>
						onclick="ym(105087875,'reachGoal','step-2'); setTimeout(() => { this.form.submit(); }, 150); return false;" <?php } ?>
					<?php
					if ( 3 === $step ) {
						?>
						onclick="ym(105087875,'reachGoal','step-3'); setTimeout(() => { this.form.submit(); }, 150); return false;" <?php } ?>
					<?php
					if ( $is_last ) {
						?>
						onclick="ym(105087875,'reachGoal',' step-4'); setTimeout(() => { this.form.submit(); }, 150); return false;" <?php } ?>>
					<?php echo \esc_html( $is_last ? \_x( 'Finish', 'Button', 'snappbox' ) : \_x( 'Save & Continue', 'Button', 'snappbox' ) ); ?>
				</button>
			</div>
		</form>
		<?php
	}

	private function snappb_render_step_1(): void {
		$settings = \maybe_unserialize( \get_option( $this->wc_option_key ) );
		$api      = \is_array( $settings ) ? ( $settings['snappbox_api'] ?? '' ) : '';

		$this->snappb_render_form_open( 1 );
		echo '<p class="sbqs-lead">' . \esc_html_x( 'Enter your SnappBox API token', 'Lead text', 'snappbox' ) . '</p>';
		?>
		<div class="sbqs-field sbqs-row">
			<label for="sb_api"><?php echo \esc_html_x( 'API Key', 'Label', 'snappbox' ); ?></label>
			<div class="sbqs-input-row">
				<input type="text" id="sb_api" name="api" value="<?php echo \esc_attr( $api ); ?>"
					placeholder="<?php echo \esc_attr_x( 'Paste your API key…', 'Placeholder', 'snappbox' ); ?>" />
				<a class="button button-primary sbqs-btn" target="_blank" rel="noopener"
					href="<?php echo \esc_url( 'https://snapp-box.com/connect' ); ?>">
					<?php echo \esc_html_x( 'Get API Key', 'Button', 'snappbox' ); ?>
				</a>
			</div>
		</div>
		<?php
		$this->snappb_render_form_close( 1 );
	}

	private function snappb_render_step_3(): void {
		$lat = \get_option( 'snappbox_latitude', '35.8037761' );
		$lng = \get_option( 'snappbox_longitude', '51.4152466' );
		$this->snappb_render_form_open( 2 );
		echo '<p class="sbqs-lead">' . \esc_html_x( 'Place your store on the map', 'Lead text', 'snappbox' ) . '</p>';
		?>
		<?php $this->snappb_zone_alert_modal(); ?>
		<div class="sbqs-map-wrap">
			<div id="sbqs-map" class="sbqs-map"></div>
			<button type="button" id="sbqs-center-pin"
				aria-label="<?php echo \esc_attr_x( 'Set location to map center', 'ARIA', 'snappbox' ); ?>">
			</button>
		</div>
		<div class="sbqs-two">
			<div class="sbqs-field">
				<label for="sb_lat"><?php echo \esc_html_x( 'Latitude', 'Label', 'snappbox' ); ?></label>
				<input type="text" name="lat" id="sb_lat" value="<?php echo \esc_attr( $lat ); ?>" />
			</div>
			<div class="sbqs-field">
				<label for="sb_lng"><?php echo \esc_html_x( 'Longitude', 'Label', 'snappbox' ); ?></label>
				<input type="text" name="lng" id="sb_lng" value="<?php echo \esc_attr( $lng ); ?>" />
			</div>
		</div>
		<?php
		$this->snappb_render_form_close( 2 );
	}

	private function snappb_zone_alert_modal() {
		?>
		<div id="snapp-modal" style="display:none;">
			<div class="snapp-modal-content">
				<span class="snapp-close">&times;</span>
				<p id="snapp-modal-message"></p>
			</div>
		</div>
		<?php
	}

	private function snappb_render_step_4(): void {
		$settings = \maybe_unserialize( \get_option( $this->wc_option_key ) );
		if ( ! \is_array( $settings ) ) {
			$settings = array();
		}

		$store_name  = \get_option( 'snappbox_store_name', '' );
		$store_phone = \get_option( 'snappbox_store_phone', '' );

		$enabled = isset( $settings['enabled'] ) ? $settings['enabled'] : 'yes';
		$title   = isset( $settings['title'] ) ? $settings['title'] : \__( 'SnappBox Shipping', 'snappbox' );

		$this->snappb_render_form_open( 3 );
		echo '<p class="sbqs-lead">' . \esc_html_x( 'Store information & activation', 'Lead text', 'snappbox' ) . '</p>';
		?>
		<div class="sbqs-grid">
			<div class="sbqs-field">
				<label for="sb_store_name"><?php echo \esc_html_x( 'Store name', 'Label', 'snappbox' ); ?></label>
				<input type="text" id="sb_store_name" name="store_name" value="<?php echo \esc_attr( $store_name ); ?>" />
			</div>
			<div class="sbqs-field">
				<label for="sb_store_phone"><?php echo \esc_html_x( 'Mobile number', 'Label', 'snappbox' ); ?></label>
				<input type="text" id="sb_store_phone" name="store_phone" value="<?php echo \esc_attr( $store_phone ); ?>" placeholder="0912…" />
			</div>
			<div class="sbqs-field">
				<label for="sb_method_title"><?php echo \esc_html_x( 'Shipping method title', 'Label', 'snappbox' ); ?></label>
				<input type="text" id="sb_method_title" name="method_title" value="<?php echo \esc_attr( $title ); ?>"
					placeholder="<?php echo \esc_attr_x( 'SnappBox Shipping', 'Placeholder', 'snappbox' ); ?>" />
			</div>
			<label class="sbqs-check">
				<input type="checkbox" name="enabled" value="yes" <?php \checked( $enabled === 'yes' ); ?> />
				<?php echo \esc_html_x( 'Enable this shipping method', 'Checkbox', 'snappbox' ); ?>
			</label>
		</div>
		<?php
		$this->snappb_render_form_close( 3 );
	}

	private function snappb_render_step_5(): void {
		$settings = \maybe_unserialize( \get_option( $this->wc_option_key ) );
		if ( ! \is_array( $settings ) ) {
			$settings = array();
		}

		$ondelivery    = ( isset( $settings['ondelivery'] ) && $settings['ondelivery'] === 'yes' );
		$free_delivery = $settings['free_delivery'] ?? '';
		$base_cost     = $settings['base_cost'] ?? '';

		$this->snappb_render_form_open( 4 );
		echo '<p class="sbqs-lead">' . \esc_html_x( 'Other settings & rates', 'Lead text', 'snappbox' ) . '</p>';
		?>
		<div class="sbqs-grid">
			<label class="sbqs-check">
				<input type="checkbox" name="ondelivery" value="yes" <?php \checked( $ondelivery ); ?> />
				<?php echo \esc_html_x( 'Pay on SnappBox delivery', 'Checkbox', 'snappbox' ); ?>
			</label>
			<div class="sbqs-field">
				<label for="sb_free_delivery"><?php echo \esc_html_x( 'Free delivery threshold', 'Label', 'snappbox' ); ?></label>
				<input type="text" id="sb_free_delivery" name="free_delivery" value="<?php echo \esc_attr( $free_delivery ); ?>" />
			</div>
			<div class="sbqs-field">
				<label for="sb_base_cost"><?php echo \esc_html_x( 'Base cost', 'Label', 'snappbox' ); ?></label>
				<input type="text" id="sb_base_cost" name="base_cost" value="<?php echo \esc_attr( $base_cost ); ?>" />
			</div>
		</div>
		<?php
		$this->snappb_render_form_close( 4, true );
	}

	public function snappb_handle_save(): void {
		if ( ! \current_user_can( 'manage_woocommerce' ) ) {
			\wp_die(
				\esc_html__( 'Forbidden', 'snappbox' ),
				'',
				array( 'response' => 403 )
			);
		}

		\check_admin_referer( $this->nonce_action, '_snappbox_qs_nonce' );

		$step_raw = isset( $_POST['step'] ) ? \sanitize_text_field( \wp_unslash( $_POST['step'] ) ) : '1';
		$step     = (int) $step_raw;

		$settings = \maybe_unserialize( \get_option( $this->wc_option_key ) );
		if ( ! \is_array( $settings ) ) {
			$settings = array();
		}

		$config = $this->get_wizard_config();
		if ( ! isset( $config[ $step ] ) ) {
			$this->snappb_redirect_step( 1 );
		}

		$settings = \maybe_unserialize( \get_option( $this->wc_option_key, array() ) );
		if ( ! \is_array( $settings ) ) {
			$settings = array();
		}

		foreach ( $config[ $step ]['fields'] as $key => $field ) {
			$raw_value = isset( $_POST[ $key ] ) ? \wp_unslash( $_POST[ $key ] ) : '';
			$value     = ( 'number' === $field['type'] ) ? $this->snappb_normalize_number( $raw_value ) : \sanitize_text_field( $raw_value );
			
			if ( ! empty( $field['option_key'] ) ) {
				\update_option( $field['option_key'], $value );
			}
			
			if ( ! empty( $field['settings_key'] ) ) {
				$settings[ $field['settings_key'] ] = $value;
			}
		}

		\update_option( $this->wc_option_key, $settings );

		if ( 4 === $step ) {
			\wp_safe_redirect(
				\add_query_arg(
					array(
						'page'    => 'wc-settings',
						'tab'     => 'shipping',
						'section' => 'snappbox_shipping_method',
					),
					\admin_url( 'admin.php' )
				)
			);
			exit;
		}

		$this->snappb_redirect_step( $step + 1 );
	}

	private function get_wizard_config(): array {
		return array(
			1 => array(
				'fields' => array(
					'api' => array( 'type' => 'text', 'settings_key' => 'snappbox_api' )
				)
			),
			2 => array(
				'fields' => array(
					'lat' => array( 'type' => 'number', 'option_key' => 'snappbox_latitude', 'settings_key' => 'snappbox_latitude' ),
					'lng' => array( 'type' => 'number', 'option_key' => 'snappbox_longitude', 'settings_key' => 'snappbox_longitude' )
				)
			),
			3 => array(
				'fields' => array(
					'store_name'   => array( 'type' => 'text', 'option_key' => 'snappbox_store_name', 'settings_key' => 'snappbox_store_name' ),
					'store_phone'  => array( 'type' => 'text', 'option_key' => 'snappbox_store_phone', 'settings_key' => 'snappbox_store_phone' ),
					'method_title' => array( 'type' => 'text', 'settings_key' => 'title' ),
					'enabled'      => array( 'type' => 'text', 'settings_key' => 'enabled' )
				)
			),
			4 => array(
				'fields' => array(
					'ondelivery'    => array( 'type' => 'text', 'settings_key' => 'ondelivery' ),
					'free_delivery' => array( 'type' => 'text', 'settings_key' => 'free_delivery' ),
					'base_cost'     => array( 'type' => 'text', 'settings_key' => 'base_cost' )
				)
			),
			// AUTO_APPEND_STEPS (Reserved for Elite Automation)
		);
	}

	/**
	 * Redirect to a specific wizard step.
	 *
	 * @param int $n Step number.
	 */
	private function snappb_redirect_step( $n ): void {
		\wp_safe_redirect( $this->snappb_url_for_step( $n ) );
		exit;
	}

	/**
	 * Normalize numbers, handling Persian/Arabic digits.
	 *
	 * @param mixed $s Input string.
	 * @return string Normalized string.
	 */
	private function snappb_normalize_number( $s ): string {
		$s = \trim( (string) $s );
		if ( '' === $s ) {
			return '';
		}
		$map = array(
			'۰' => '0',
			'۱' => '1',
			'۲' => '2',
			'۳' => '3',
			'۴' => '4',
			'۵' => '5',
			'۶' => '6',
			'۷' => '7',
			'۸' => '8',
			'۹' => '9',
			'٠' => '0',
			'١' => '1',
			'٢' => '2',
			'٣' => '3',
			'٤' => '4',
			'٥' => '5',
			'٦' => '6',
			'٧' => '7',
			'٨' => '8',
			'٩' => '9',
		);
		$s   = \strtr( $s, $map );
		$s   = \str_replace( ',', '.', $s );
		$s   = \preg_replace( '/[^0-9.\-+eE]/', '', $s );
		return $s;
	}

	/**
	 * Get the SnappBox logo SVG markup.
	 *
	 * @return string SVG markup.
	 */
	private function snappb_get_logo_svg(): string {
		$base_url = \defined( 'SNAPPBOX_URL' ) ? \trailingslashit( SNAPPBOX_URL ) : '';
		$src      = $base_url . 'assets/img/sb-log.svg';
		return '<div class="sb-logo"><img src="' . \esc_url( $src ) . '" class="sb-logo" alt="' . \esc_attr_x( 'SnappBox', 'Logo alt', 'snappbox' ) . '"/></div>';
	}
}
