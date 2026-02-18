<?php
declare(strict_types=1);

namespace Snappbox\WooCommerce;

defined('ABSPATH') || exit;

/**
 * Enterprise Order Column functionality for SnappBox.
 */
class OrderColumn {
    private $column_id          = 'order_status_check';
    private $meta_key           = '_snappbox_last_api_response';
    private $date_column_id     = 'snappbox_date';

    public function boot(): void {
        \add_filter('manage_woocommerce_page_wc-orders_columns', [$this, 'add_columns'], 20);
        \add_action('manage_woocommerce_page_wc-orders_custom_column', [$this, 'render_hpos_column'], 20, 2);

        \add_filter('manage_edit-shop_order_columns', [$this, 'add_columns'], 20);
        \add_action('manage_shop_order_posts_custom_column', [$this, 'render_legacy_column'], 20, 2);
    }

    public function add_columns($columns): array {
        $new = [];
        foreach ($columns as $key => $label) {
            $new[$key] = $label;
            if ('order_total' === $key) {
                $new[$this->date_column_id] = \__('SnappBox Date', 'snappbox');
                $new[$this->column_id]      = \__('SnappBox', 'snappbox');
            }
        }
        return $new;
    }

    public function render_hpos_column($column, $order): void {
        if (!($order instanceof \WC_Order)) return;
        $this->render_cell($column, $order);
    }

    public function render_legacy_column($column, $post_id): void {
        $order = \wc_get_order($post_id);
        if ($order) $this->render_cell($column, $order);
    }

    private function render_cell($column, $order): void {
        if ($column === $this->date_column_id) {
            $date = $order->get_meta('_snappbox_day');
            $time = $order->get_meta('_snappbox_time');
            echo $date ? \esc_html($date . ' ' . $time) : '—';
        } elseif ($column === $this->column_id) {
            $meta = $order->get_meta($this->meta_key);
            echo $meta ? \esc_html($meta->statusText ?? 'Processing') : '—';
        }
    }
}
