<?php
declare(strict_types=1);

namespace Snappbox\WooCommerce;

defined('ABSPATH') || exit;

/**
 * Enterprise On-Delivery Payment Gateway.
 */
class PaymentGateway extends \WC_Payment_Gateway {

    public function boot(): void {
        \add_filter('woocommerce_payment_gateways', function ($gateways) {
            $gateways[] = self::class;
            return $gateways;
        });
    }

    public function __construct() {
        $this->id                 = 'snappbox_gateway';
        $this->method_title       = \__('SnappBox On-Delivery', 'snappbox');
        $this->method_description = \__('Pay with SnappBox on delivery.', 'snappbox');
        $this->has_fields         = false;

        $this->init_form_fields();
        $this->init_settings();

        $this->title       = $this->get_option('title');
        $this->description = $this->get_option('description');

        \add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }

    public function init_form_fields(): void {
        $this->form_fields = [
            'enabled' => ['title' => \__('Enable/Disable', 'snappbox'), 'type' => 'checkbox', 'default' => 'yes'],
            'title'   => ['title' => \__('Title', 'snappbox'), 'type' => 'text', 'default' => \__('SnappBox On Delivery', 'snappbox')],
        ];
    }

    public function process_payment($order_id): array {
        $order = \wc_get_order($order_id);
        $order->update_status('on-hold', \__('Awaiting SnappBox delivery', 'snappbox'));
        \wc_reduce_stock_levels($order_id);
        \WC()->cart->empty_cart();

        return [
            'result'   => 'success',
            'redirect' => $this->get_return_url($order),
        ];
    }
}
