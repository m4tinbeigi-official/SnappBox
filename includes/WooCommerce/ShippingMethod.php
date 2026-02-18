<?php
declare(strict_types=1);

namespace Snappbox\WooCommerce;

use Snappbox\Core\Geo\PolygonValidator;
use Snappbox\Core\Diagnostics\Logger;
use Snappbox\Core\UI\NoticeManager;
use Snappbox\WooCommerce\Shipping\PriceCalculator;

defined('ABSPATH') || exit;

/**
 * Enterprise SnappBox Shipping Method for WooCommerce.
 */
class ShippingMethod extends \WC_Shipping_Method {

    /** @var PolygonValidator */
    private $validator;

    /** @var Logger */
    private $logger;

    /** @var NoticeManager */
    private $notices;

    /** @var PriceCalculator */
    private $prices;

    public function __construct(
        PolygonValidator $validator,
        Logger $logger,
        NoticeManager $notices,
        PriceCalculator $prices,
        $instance_id = 0
    ) {
        $this->validator   = $validator;
        $this->logger      = $logger;
        $this->notices     = $notices;
        $this->prices      = $prices;
        $this->id          = 'snappbox_shipping_method';
        $this->instance_id = \absint($instance_id);
        $this->method_title = \__('SnappBox Elite', 'snappbox');
        $this->supports    = ['shipping-zones', 'instance-settings', 'settings'];

        $this->init_form_fields();
        $this->init_settings();
        $this->enabled = $this->get_option('enabled');
        $this->title   = $this->get_option('title');

        \add_action('woocommerce_update_options_shipping_' . $this->id, [$this, 'process_admin_options']);
        \add_action('admin_notices', [$this, 'check_wallet_health']);
    }

    public function boot(): void {
        \add_filter('woocommerce_shipping_methods', [$this, 'add_method']);
    }

    public function add_method($methods): array {
        $methods[$this->id] = \get_class($this);
        return $methods;
    }

    public function init_form_fields(): void {
        $this->form_fields = [
            'enabled' => [
                'title'   => \__('Enable', 'snappbox'),
                'type'    => 'checkbox',
                'default' => 'yes'
            ],
            'title' => [
                'title'   => \__('Title', 'snappbox'),
                'type'    => 'text',
                'default' => \__('SnappBox Elite Delivery', 'snappbox')
            ],
            'free_delivery' => [
                'title'   => \__('Free Delivery Threshold', 'snappbox'),
                'type'    => 'number',
                'description' => \__('Minimum order value for free delivery.', 'snappbox')
            ],
            'base_cost' => [
                'title'   => \__('Fixed Price (Optional)', 'snappbox'),
                'type'    => 'text',
                'description' => \__('If set, this price overrides dynamic calculations.', 'snappbox')
            ],
            'polygon_coords' => [
                'title' => \__('Zone (GeoJSON)', 'snappbox'),
                'type'  => 'textarea',
                'description' => \__('GeoJSON representation of the delivery zone.', 'snappbox')
            ]
        ];
    }

    public function calculate_shipping($package = []): void {
        $subtotal = (float) (\WC()->cart->get_subtotal() ?? 0);
        $cost = $this->prices->calculate($subtotal, $this->settings);

        $this->add_rate([
            'id'    => $this->get_rate_id(),
            'label' => $this->title,
            'cost'  => $cost ?? 0,
        ]);
    }

    public function check_wallet_health(): void {
        if (!\current_user_can('manage_woocommerce')) return;
        
        $wallet = new \Snappbox\API\WalletBalance();
        $res = $wallet->snappb_check_balance();
        
        if ($res && isset($res['balance']) && (float)$res['balance'] < 10000) {
            $this->notices->add_elite_notice(
                \__('Warning: Your SnappBox wallet balance is extremely low. Deliveries might be interrupted.', 'snappbox'),
                'warning'
            );
        }
    }
}
