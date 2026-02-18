<?php
declare(strict_types=1);

namespace Snappbox\Admin;

defined('ABSPATH') || exit;

/**
 * Enterprise Order Management for SnappBox.
 */
class OrderAdmin {

    public function boot(): void {
        \add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        \add_action('woocommerce_admin_order_data_after_order_details', [$this, 'display_order_admin_box'], 20, 1);
        \add_action('wp_ajax_snappb_create_order', [$this, 'handle_create_order']);
        \add_action('wp_ajax_snappb_cancel_order', [$this, 'handle_cancel_order']);
        \add_action('wp_ajax_snappb_get_pricing',  [$this, 'handle_get_pricing']);
    }

    public function enqueue_assets(): void {
        $base_url = \defined('SNAPPBOX_URL') ? \trailingslashit(SNAPPBOX_URL) : '';
        $base_dir = \defined('SNAPPBOX_DIR') ? \trailingslashit(SNAPPBOX_DIR) : '';

        \wp_enqueue_style('maplibre-gl', $base_url . 'assets/css/leaflet.css', [], '1.9.4');
        \wp_enqueue_script('maplibre-gl', $base_url . 'assets/js/leaflet.js', [], '1.9.4', true);

        \wp_enqueue_style('snappbox-admin', $base_url . 'assets/css/admin-snappbox.css', [], '1.2.0');
        \wp_enqueue_script('snappbox-admin', $base_url . 'assets/js/admin-snappbox.js', ['jquery', 'maplibre-gl'], '1.2.0', true);

        \wp_localize_script('snappbox-admin', 'SNAPPBOX_GLOBAL', [
            'ajaxUrl'      => \admin_url('admin-ajax.php'),
            'nonce'        => \wp_create_nonce('snappbox_admin_actions'),
            'rtlPluginUrl' => $base_url . 'assets/js/mapbox-gl-rtl-text.js',
            'mapStyleUrl'  => 'https://tile.snappmaps.ir/styles/snapp-style-v4.1.2/style.json',
        ]);
    }

    public function display_order_admin_box($order): void {
        $nonce = \wp_create_nonce('snappbox_admin_actions');
        $latitude  = $order->get_meta('_customer_latitude');
        $longitude = $order->get_meta('_customer_longitude');

        if ($latitude && $longitude) {
            ?>
            <div class="order_data_column_fullwidth snappbox-elite-section">
                <?php \wp_nonce_field('snappbox_admin_actions', 'nonce'); ?>
                <h3><img src="<?php echo \esc_url(SNAPPBOX_URL); ?>assets/img/sb-log.svg" style="height: 20px; vertical-align: middle; margin-right: 10px;"> SnappBox Elite</h3>
                
                <div id="admin-osm-map" class="sb-admin-map" data-lat="<?php echo \esc_attr($latitude); ?>" data-lng="<?php echo \esc_attr($longitude); ?>" style="height: 300px; border-radius: 12px; margin-bottom: 15px;"></div>
                
                <div class="elite-card" style="padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0;">
                    <p><strong>Address:</strong> <?php echo \esc_html($order->get_shipping_address_1()); ?></p>
                    <div id="snappbox-admin-context" data-nonce="<?php echo \esc_attr($nonce); ?>" data-woo-order-id="<?php echo (int) $order->get_id(); ?>"></div>
                    <?php $this->render_action_buttons($order, $nonce); ?>
                </div>
            </div>
            <?php
        }
    }

    private function render_action_buttons($order, $nonce): void {
        $snappbox_id = $order->get_meta('_snappbox_order_id');
        if (!$snappbox_id) {
            echo '<button id="snappbox-pricing-order" data-order-id="'.(int)$order->get_id().'" class="button button-primary">Get Elite Pricing</button>';
        } else {
            echo '<p><strong>SnappBox ID:</strong> ' . \esc_html($snappbox_id) . '</p>';
            echo '<button id="snappbox-cancel-order" data-order-id="'.\esc_attr($snappbox_id).'" data-woo-order-id="'.(int)$order->get_id().'" class="button button-secondary">Cancel Order</button>';
        }
    }

    public function handle_create_order(): void {
        \check_ajax_referer('snappbox_admin_actions', 'nonce');
        $order_id = \absint($_POST['order_id'] ?? 0);
        if (!$order_id || !\current_user_can('manage_woocommerce')) {
            \wp_send_json_error('Forbidden or invalid order');
        }

        $api = new \Snappbox\API\CreateOrder();
        $response = $api->snappb_handle_create_order($order_id, \sanitize_text_field($_POST['voucher_code'] ?? ''));
        \wp_send_json($response);
    }

    public function handle_cancel_order(): void {
        \check_ajax_referer('snappbox_admin_actions', 'nonce');
        $order_id = \sanitize_text_field($_POST['order_id'] ?? '');
        $woo_id = \absint($_POST['woo_order_id'] ?? 0);
        
        if (!$woo_id || !\current_user_can('manage_woocommerce')) {
            \wp_send_json_error('Forbidden');
        }

        $api = new \Snappbox\API\CancelOrder();
        $response = $api->cancel($order_id);
        
        if ($response && !empty($response['success'])) {
            \delete_post_meta($woo_id, '_snappbox_order_id');
            \wp_send_json_success('Order cancelled successfully');
        } else {
            \wp_send_json_error($response['message'] ?? 'Cancellation failed');
        }
    }

    public function handle_get_pricing(): void {
        \check_ajax_referer('snappbox_admin_actions', 'nonce');
        $order_id = \absint($_POST['order_id'] ?? 0);
        if (!$order_id) \wp_send_json_error('Order ID missing');

        $pricing_api = new \Snappbox\API\Pricing();
        // Simplified for brevity, usually you'd get city map etc.
        $response = $pricing_api->snappb_get_pricing($order_id, null, '', '', '', \sanitize_text_field($_POST['voucher_code'] ?? ''));
        \wp_send_json($response);
    }
}
