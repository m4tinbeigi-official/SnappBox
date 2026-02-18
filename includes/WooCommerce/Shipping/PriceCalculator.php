<?php
declare(strict_types=1);

namespace Snappbox\WooCommerce\Shipping;

defined('ABSPATH') || exit;

/**
 * Enterprise Price Calculator for SnappBox Shipping.
 */
class PriceCalculator {

    /**
     * Calculate the final shipping cost based on distance, subtotal, and settings.
     *
     * @param float $subtotal Current cart subtotal
     * @param array $settings Plugin settings
     * @param array $extra Optional data (e.g., dynamic price from API)
     * @return float|null The calculated cost, or null if snappbox is not applicable.
     */
    public function calculate(float $subtotal, array $settings, array $extra = []): ?float {
        // 1. Check for Free Delivery Threshold
        $free_threshold = (float) ($settings['free_delivery'] ?? 0);
        if ($free_threshold > 0 && $subtotal >= $free_threshold) {
            return 0.0;
        }

        // 2. Check for Fixed Cost Override
        $fixed_cost = $settings['base_cost'] ?? '';
        if ($fixed_cost !== '') {
            return (float) $fixed_cost;
        }

        // 3. Fallback to Dynamic Price from API if provided
        return (float) ($extra['dynamic_price'] ?? 0);
    }
}
