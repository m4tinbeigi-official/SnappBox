<?php
declare(strict_types=1);

namespace Snappbox\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Enterprise Schedule Modal for SnappBox.
 */
class ScheduleModal {
	private $allowed_days = array( 'saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday' );

	public function boot(): void {
		\add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		\add_action( 'wp_ajax_snappb_save_schedule', array( $this, 'handle_save_schedule' ) );
	}

	public function enqueue_assets(): void {
		\wp_enqueue_script(
			'SnappBoxData',
			SNAPPBOX_URL . 'assets/js/snappbox-schedule-script.js',
			array( 'jquery' ),
			'1.2.0',
			true
		);

		\wp_localize_script(
			'SnappBoxData',
			'SnappBoxData',
			array(
				'ajax_url' => \admin_url( 'admin-ajax.php' ),
				'nonce'    => \wp_create_nonce( 'snappbox_schedule_nonce' ),
			)
		);
	}

	public function handle_save_schedule(): void {
		\check_ajax_referer( 'snappbox_schedule_nonce', 'nonce' );

		if ( ! \current_user_can( 'manage_woocommerce' ) ) {
			\wp_send_json_error( array( 'message' => 'Forbidden' ), 403 );
		}

		$day = \sanitize_text_field( $_POST['day'] ?? '' );
		if ( ! \in_array( $day, $this->allowed_days, true ) ) {
			\wp_send_json_error( array( 'message' => 'Invalid day' ), 400 );
		}

		$slots              = \map_deep( $_POST['slots'] ?? array(), 'sanitize_text_field' );
		$saved_data         = \get_option( 'snappbox_schedule', array() );
		$saved_data[ $day ] = $slots;

		\update_option( 'snappbox_schedule', $saved_data );
		\wp_send_json_success( array( 'message' => 'Schedule saved!' ) );
	}
}
