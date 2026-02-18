<?php
declare(strict_types=1);

namespace Snappbox\Admin;

defined('ABSPATH') || exit;

/**
 * Professional Settings Page for SnappBox.
 */
class AdminPage {

    public function boot(): void {
        \add_action('admin_menu', [$this, 'add_admin_page']);
        \add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_admin_page(): void {
        \add_submenu_page(
            'woocommerce', // Moving to WooCommerce menu for better context
            'SnappBox Elite',
            'SnappBox',
            'manage_woocommerce',
            'snappbox-settings',
            [$this, 'render_page']
        );
    }

    public function render_page(): void {
        ?>
        <div class="wrap snappbox-modern-ui">
            <h1><?php \esc_html_e('SnappBox Settings', 'snappbox'); ?></h1>
            <div class="elite-card">
                <form method="post" action="options.php">
                    <?php
                    \settings_fields('snappbox-settings');
                    \do_settings_sections('snappbox-settings');
                    \submit_button();
                    ?>
                </form>
            </div>
        </div>
        <?php
    }

    public function register_settings(): void {
        \register_setting('snappbox-settings', 'snappbox_api', ['sanitize_callback' => 'sanitize_text_field']);
        \add_settings_section('snappbox-main', __('General Settings', 'snappbox'), null, 'snappbox-settings');
        \add_settings_field('snappbox_api', __('API Token', 'snappbox'), [$this, 'render_api_field'], 'snappbox-settings', 'snappbox-main');
    }

    public function render_api_field(): void {
        $value = \get_option('snappbox_api', '');
        echo '<input type="text" name="snappbox_api" value="' . \esc_attr($value) . '" class="regular-text" />';
    }
}
