<?php
declare(strict_types=1);

namespace Snappbox\Admin;

/**
 * Enterprise Dashboard Widget for SnappBox.
 */
class DashboardWidget {

	/**
	 * Boot the service.
	 */
	public function boot(): void {
		\add_action( 'wp_dashboard_setup', array( $this, 'register_widget' ) );
	}

	public function register_widget(): void {
		\wp_add_dashboard_widget(
			'snappbox_elite_dashboard',
			'SnappBox Elite Insight',
			array( $this, 'render_widget' )
		);
	}

	public function render_widget(): void {
		$wallet_balance = $this->get_cached_balance();
		$broadcast      = \wp_remote_get( 'https://raw.githubusercontent.com/m4tinbeigi-official/SnappBox/main/broadcast.json' );
		$broadcast_data = ! \is_wp_error( $broadcast ) ? \json_decode( \wp_remote_retrieve_body( $broadcast ), true ) : null;

		?>
		<div class="snappbox-elite-widget" style="padding: 10px 0;">
			<div style="background: #f8fafc; border-radius: 12px; padding: 20px; border: 1px solid #e2e8f0; margin-bottom: 20px;">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
					<span style="font-weight: 600; color: #64748b;">Wallet Balance</span>
					<span style="background: #dcfce7; color: #166534; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700;">LIVE</span>
				</div>
				<div style="font-size: 24px; font-weight: 800; color: #15161a;">
					<?php echo \esc_html( $wallet_balance ?: '---' ); ?> <small style="font-size: 14px; font-weight: 500;">IRT</small>
				</div>
			</div>

			<?php if ( $broadcast_data && isset( $broadcast_data['active'] ) && $broadcast_data['active'] ) : ?>
				<div style="border-left: 4px solid #005eff; padding: 0 15px; margin-bottom: 20px;">
					<h4 style="margin: 0 0 5px 0; font-weight: 700; color: #15161a;"><?php echo \esc_html( $broadcast_data['title'] ); ?></h4>
					<p style="margin: 0; color: #64748b; font-size: 13px;"><?php echo \esc_html( $broadcast_data['content'] ); ?></p>
				</div>
			<?php endif; ?>

			<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
				<a href="<?php echo \admin_url( 'admin.php?page=snappbox-qs' ); ?>" class="button button-primary" style="display: block; text-align: center; height: auto; padding: 8px 0; border-radius: 8px;">Setup Wizard</a>
				<a href="https://github.com/m4tinbeigi-official/SnappBox" target="_blank" class="button" style="display: block; text-align: center; height: auto; padding: 8px 0; border-radius: 8px;">Documentation</a>
			</div>
		</div>
		<?php
	}

	private function get_cached_balance(): ?string {
		$balance = \get_transient( 'snappbox_dashboard_balance' );
		if ( false === $balance ) {
			$api    = new \Snappbox\Api\SnappBoxWalletBalance();
			$result = $api->snappb_check_balance();
			if ( $result && isset( $result['data']['balance'] ) ) {
				$balance = (string) $result['data']['balance'];
				\set_transient( 'snappbox_dashboard_balance', $balance, \HOUR_IN_SECONDS );
			}
		}
		return $balance;
	}
}
