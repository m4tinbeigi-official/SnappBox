<?php
declare(strict_types=1);
declare(strict_types=1);

namespace Snappbox\Core;

/**
 * Enterprise Bootstrap class for SnappBox.
 */
class App {

    /**
     * Initialize the application.
     */
    public static function init(): void {
        try {
            // Boot Core Services via Container
            $services = [
                \Snappbox\Admin\SetupWizard::class,
                \Snappbox\Admin\DashboardWidget::class,
                \Snappbox\Admin\OrderAdmin::class,
                \Snappbox\Admin\AdminPage::class,
                \Snappbox\WooCommerce\ShippingMethod::class,
                \Snappbox\WooCommerce\OrderColumn::class,
                \Snappbox\WooCommerce\PaymentGateway::class,
                \Snappbox\Frontend\CheckoutMap::class,
                \Snappbox\Frontend\ScheduleModal::class,
                \Snappbox\API\Broadcast::class,
            ];

            foreach ($services as $service_class) {
                // Professional DI-based instantiation
                $service = Container::instance()->get($service_class);
                
                // If service has a boot method, call it
                if (method_exists($service, 'boot')) {
                    $service->boot();
                }
            }

            self::init_global_hooks();

        } catch (\Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('SnappBox Boot Error: ' . $e->getMessage());
            }
        }
    }

    /**
     * Initialize global hooks that don't belong to a specific component.
     */
    private static function init_global_hooks(): void {
        \add_action('plugins_loaded', [self::class, 'plugins_loaded']);
        
        \add_action('before_woocommerce_init', function () {
            if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', SNAPPBOX_DIR . 'snappbox.php', true);
            }
        });
    }

    public static function plugins_loaded(): void {
        \load_plugin_textdomain('snappbox', false, dirname(plugin_basename(SNAPPBOX_DIR . 'snappbox.php')) . '/languages/');
    }
}
