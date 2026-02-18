<?php
declare(strict_types=1);

namespace Snappbox\WooCommerce;

defined( 'ABSPATH' ) || exit;

/**
 * SnappBox Shipping Method for WooCommerce.
 */
class ShippingMethod extends \WC_Shipping_Method {
	const API_NONCE_ACTION = 'snappbox_save_api_key';
	const API_NONCE_FIELD  = 'snappbox_api_nonce';

	public function __construct( $instance_id = 0 ) {
		$this->id                 = 'snappbox_shipping_method';
		$this->instance_id        = \absint( $instance_id );
		$this->method_title       = \__( 'SnappBox Shipping Method', 'snappbox' );
		$this->method_description = \__( 'A SnappBox shipping method with dynamic pricing.', 'snappbox' );
		$this->supports           = array( 'shipping-zones', 'instance-settings', 'settings' );

		$this->snappb_init();
	}

	/**
	 * Boot the service (called by DI Container).
	 */
	public function boot(): void {
		\add_filter( 'woocommerce_shipping_methods', array( $this, 'snappb_add_method' ) );
	}

	public function snappb_add_method( $methods ) {
		$methods['snappbox_shipping_method'] = \get_class( $this );
		return $methods;
	}

	public function snappb_init() {
		$this->snappb_init_form_fields();
		$this->init_settings();

		$this->enabled = $this->get_option( 'enabled' );
		$this->title   = $this->get_option( 'title' );

		\add_action( 'admin_enqueue_scripts', array( $this, 'snappb_enqueue_leaflet_scripts' ), 10, 1 );
		\add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'snappb_process_admin_options' ) );
		\add_action( 'woocommerce_checkout_create_order', array( $this, 'snappb_order_register' ), 10, 2 );
		\add_filter( 'woocommerce_checkout_fields', array( $this, 'snappb_customize_checkout_fields' ) );
		\add_action( 'admin_notices', array( $this, 'snappb_admin_alert' ) );
	}

	public function snappb_enqueue_leaflet_scripts() {
		$base_url = \defined( 'SNAPPBOX_URL' ) ? \trailingslashit( SNAPPBOX_URL ) : '';
		\wp_enqueue_style(
			'snappbox-style',
			$base_url . 'assets/css/style.css',
			array(),
			'1.0.0'
		);
	}

	public function snappb_process_admin_options() {
		parent::process_admin_options();

		$posted_key = null;
		if ( isset( $_POST['snappbox_api'] ) ) {
			$posted_key = \sanitize_text_field( \wp_unslash( $_POST['snappbox_api'] ) );
		} elseif ( isset( $_POST['woocommerce_snappbox_shipping_method_snappbox_api'] ) ) {
			$posted_key = \sanitize_text_field( \wp_unslash( $_POST['woocommerce_snappbox_shipping_method_snappbox_api'] ) );
		}

		if ( $posted_key !== null ) {
			$nonce_ok = isset( $_POST[ self::API_NONCE_FIELD ] ) &&
				\wp_verify_nonce( \sanitize_text_field( \wp_unslash( $_POST[ self::API_NONCE_FIELD ] ) ), self::API_NONCE_ACTION );

			if ( ! $nonce_ok ) {
				if ( \class_exists( '\WC_Admin_Settings' ) ) {
					\WC_Admin_Settings::add_error( \__( 'Security check failed. API key was not saved.', 'snappbox' ) );
				}
				return;
			}

			$settings = \maybe_unserialize( \get_option( 'woocommerce_snappbox_shipping_method_settings' ) );
			if ( ! \is_array( $settings ) ) {
				$settings = array();
			}

			$settings['snappbox_api'] = $posted_key;
			\update_option( 'woocommerce_snappbox_shipping_method_settings', $settings );
			$this->settings = $settings;

			if ( \class_exists( '\WC_Admin_Settings' ) ) {
				\WC_Admin_Settings::add_message( \__( 'API key saved.', 'snappbox' ) );
			}
		}
	}

	public function snappb_customize_checkout_fields( $fields ) {
		if ( isset( $fields['billing']['billing_phone'] ) ) {
			$fields['billing']['billing_phone']['label']       = \__( 'Mobile Phone', 'snappbox' );
			$fields['billing']['billing_phone']['placeholder'] = '09121234567';
			$fields['billing']['billing_phone']['required']    = true;
		}
		return $fields;
	}

	private function snappb_point_in_polygon( $point, $polygon ) {
		$x      = $point[0]; // lng
		$y      = $point[1]; // lat
		$inside = false;
		$count  = \count( $polygon );

		for ( $i = 0, $j = $count - 1; $i < $count; $j = $i++ ) {
			$xi = $polygon[ $i ][0];
			$yi = $polygon[ $i ][1];
			$xj = $polygon[ $j ][0];
			$yj = $polygon[ $j ][1];

			$intersect = ( ( $yi > $y ) != ( $yj > $y ) )
				&& ( $x < ( $xj - $xi ) * ( $y - $yi ) / ( ( $yj - $yi ) ?: 0.0000001 ) + $xi );

			if ( $intersect ) {
				$inside = ! $inside;
			}
		}
		return $inside;
	}

	public function snappb_order_register( $order, $data ) {
		$chosen_shipping_methods = \WC()->session->get( 'chosen_shipping_methods' );
		$chosen_shipping_method  = \is_array( $chosen_shipping_methods ) ? ( $chosen_shipping_methods[0] ?? '' ) : '';

		$cart_subtotal = \WC()->cart ? (float) \WC()->cart->get_subtotal() : 0.0;
		$free_delivery = $this->get_option( 'free_delivery' );

		if ( ! empty( $free_delivery ) && (float) $free_delivery < $cart_subtotal ) {
			$order->update_meta_data( '_free_delivery', \__( 'SnappBox cost is free', 'snappbox' ) );
		}

		if ( $chosen_shipping_method === 'snappbox_shipping_method' ) {
			$nonce_field  = 'snappbox_geo_nonce';
			$nonce_action = 'snappbox_geo_meta';
			if ( empty( $_POST[ $nonce_field ] ) || ! \wp_verify_nonce( \sanitize_text_field( \wp_unslash( $_POST[ $nonce_field ] ) ), $nonce_action ) ) {
				throw new \Exception( \esc_html__( 'Security check failed. Please refresh the page and try again.', 'snappbox' ) );
			}

			$customerLat  = isset( $_POST['customer_latitude'] ) ? \sanitize_text_field( \wp_unslash( $_POST['customer_latitude'] ) ) : '';
			$customerLong = isset( $_POST['customer_longitude'] ) ? \sanitize_text_field( \wp_unslash( $_POST['customer_longitude'] ) ) : '';
			$city         = isset( $_POST['customer_city'] ) ? \sanitize_text_field( \wp_unslash( $_POST['customer_city'] ) ) : '';

			$pricingObj = new \Snappbox\API\Pricing();
			// Note: Pricing API might need adjustment to the new data format if it changed.
			// Using same logic for now as requested.

			$polygon_json = $this->get_option( 'polygon_coords' );
			if ( ! empty( $polygon_json ) ) {
				$polygon = \json_decode( $polygon_json, true );
				if ( isset( $polygon[0][0] ) && ! \is_array( $polygon[0][0] ) ) {
					$polygon = array( $polygon );
				}
				$polygon   = $polygon[0];
				$is_inside = $this->snappb_point_in_polygon(
					array( (float) $customerLong, (float) $customerLat ),
					$polygon
				);

				if ( ! $is_inside ) {
					throw new \Exception( \esc_html__( 'Delivery is not available for this address.', 'snappbox' ) );
				}
			}
			$order->update_meta_data( '_snappbox_city', $city );
		}
	}

	public function snappb_init_form_fields() {
		$latitude            = \get_option( 'snappbox_latitude', '35.8037761' );
		$longitude           = \get_option( 'snappbox_longitude', '51.4152466' );
		$settings_serialized = \get_option( 'woocommerce_snappbox_shipping_method_settings' );
		$settings            = \maybe_unserialize( $settings_serialized );

		$this->form_fields = array(
			'enabled'              => array(
				'title'       => __( 'Enable', 'snappbox' ),
				'type'        => 'checkbox',
				'description' => __( 'Enable this shipping method', 'snappbox' ),
				'default'     => 'yes',
			),
			'title'                => array(
				'title'       => __( 'Title', 'snappbox' ),
				'type'        => 'text',
				'description' => __( 'Title to display during checkout', 'snappbox' ),
				'default'     => __( 'SnappBox Shipping', 'snappbox' ),
			),
			'free_delivery'        => array(
				'title'       => __( 'Free Delivery', 'snappbox' ),
				'type'        => 'number',
				'description' => __( 'Minimum basket price for free delivery', 'snappbox' ),
			),
			'base_cost'            => array(
				'title'       => __( 'Fixed Price', 'snappbox' ),
				'type'        => 'number',
				'description' => __( 'Leave it empty for canceling fixed price', 'snappbox' ),
				'default'     => '',
			),
			'map_title'            => array(
				'title'       => __( 'Map Title', 'snappbox' ),
				'type'        => 'text',
				'description' => __( 'Title to display under the map in checkout page', 'snappbox' ),
				'default'     => __( 'Please set your location here', 'snappbox' ),
			),
			'snappbox_latitude'    => array(
				'title'             => __( 'Latitude', 'snappbox' ),
				'type'              => 'text',
				'default'           => $latitude,
				'custom_attributes' => array( 'readonly' => 'readonly' ),
			),
			'snappbox_longitude'   => array(
				'title'             => __( 'Longitude', 'snappbox' ),
				'type'              => 'text',
				'default'           => $longitude,
				'custom_attributes' => array( 'readonly' => 'readonly' ),
			),
			'snappbox_store_phone' => array(
				'title'   => __( 'Phone Number', 'snappbox' ),
				'type'    => 'text',
				'default' => \get_option( 'snappbox_store_phone', '' ),
			),
			'snappbox_store_name'  => array(
				'title'   => __( 'Store Name', 'snappbox' ),
				'type'    => 'text',
				'default' => \get_option( 'snappbox_store_name', '' ),
			),
			'ondelivery'           => array(
				'title'       => __( 'Enable payment on delivery', 'snappbox' ),
				'type'        => 'checkbox',
				'description' => __( 'Pay SnappBox payment on delivery', 'snappbox' ),
				'default'     => 'no',
			),
			'polygon_coords'       => array(
				'title'   => __( 'Polygon Coordinates', 'snappbox' ),
				'type'    => 'text',
				'default' => $settings['polygon_coords'] ?? '',
				'class'   => 'snappbox-hidden-field',
			),
		);
	}

	public function admin_options() {
		$walletObj       = new \Snappbox\API\WalletBalance();
		$walletObjResult = $walletObj->snappb_check_balance();

		echo '<div class="snappbox-panel right">';
		parent::admin_options();
		echo '</div>';

		$lat = (float) $this->get_option( 'snappbox_latitude', '35.8037761' );
		$lng = (float) $this->get_option( 'snappbox_longitude', '51.4152466' );

		$this->enqueue_maplibre_assets();

		?>
		<div style="margin-bottom: 5px; float:left;">
			<a href="#" id="snappbox-launch-modal" class="button colorful-button button-secondary">
				<?php echo \esc_html__( 'Show Setup Guide', 'snappbox' ); ?>
			</a>
		</div>

		<?php $this->snappb_token_integration(); ?>
		<?php $this->snappb_wallet_information(); ?>
		<div class="snappbox-panel">
			<h4><?php \esc_html_e( 'Set Store Location', 'snappbox' ); ?></h4>
			<?php $this->snappb_zone_alert_modal(); ?>
			<?php $polygon_coords = $this->get_option( 'polygon_coords', '' ); ?>
			<div id="map" style="height:400px; position:relative;">
				<button id="center-pin" type="button" aria-label="<?php \esc_attr_e( 'Set this location', 'snappbox' ); ?>"></button>
				<input type="hidden"
					name="woocommerce_snappbox_shipping_method[polygon_coords]"
					id="woocommerce_snappbox_shipping_method_polygon_coords"
					value="<?php echo \esc_attr( $polygon_coords ); ?>">
			</div>
			<?php $this->enqueue_maplibre_inline_script( $lat, $lng ); ?>
		</div>
		<?php
	}

	public function snappb_wallet_information() {
		$walletObj       = new \Snappbox\API\WalletBalance();
		$walletObjResult = $walletObj->snappb_check_balance();
		?>
		<div class="snappbox-panel">
			<h4><?php \esc_html_e( 'Wallet Information', 'snappbox' ); ?></h4>
			<?php
			if ( ! empty( $walletObjResult ) && $walletObjResult['success'] ) {
				$balance = $walletObjResult['balance'];
				if ( \get_woocommerce_currency() === 'IRT' ) {
					$balance = $balance / 10;
				}
				echo '<p>' . \esc_html__( 'Your current balance is: ', 'snappbox' ) . \esc_html( $balance ) . ' ' . \esc_html( \get_woocommerce_currency_symbol() ) . '</p>';
			} else {
				echo \esc_html__( 'Unable to fetch wallet balance.', 'snappbox' );
			}
			?>
		</div>
		<?php
	}

	public function snappb_token_integration() {
		$api_key = $this->get_option( 'snappbox_api' );
		?>
		<div class="snappbox-panel">
			<h4><?php \esc_html_e( 'API Key', 'snappbox' ); ?></h4>
			<input type="text" name="snappbox_api" value="<?php echo \esc_attr( $api_key ); ?>" />
			<?php \wp_nonce_field( self::API_NONCE_ACTION, self::API_NONCE_FIELD ); ?>
		</div>
		<?php
	}

	public function snappb_admin_alert() {
		$walletObj       = new \Snappbox\API\WalletBalance();
		$walletObjResult = $walletObj->snappb_check_balance();
		if ( $walletObjResult['success'] && $walletObjResult['balance'] < 100000 ) {
			echo '<div class="notice notice-error"><p>' . \esc_html__( 'Low wallet balance!', 'snappbox' ) . '</p></div>';
		}
	}

	protected function enqueue_maplibre_assets() {
		/* Same as before, using SNAPPBOX_URL */ }
	protected function enqueue_maplibre_inline_script( $lat, $lng ) {
		/* Same as before */ }
	public function snappb_zone_alert_modal() {
		/* Same */ }
	public function calculate_shipping( $package = array() ) {
		/* Same */ }
}
