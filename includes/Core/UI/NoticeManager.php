<?php
declare(strict_types=1);

namespace Snappbox\Core\UI;

defined('ABSPATH') || exit;

/**
 * Notice Manager for Elite Styled Alerts.
 */
class NoticeManager {

    /**
     * Render a styled admin notice with elite design tokens.
     *
     * @param string $message
     * @param string $type 'success', 'error', 'warning', 'info'
     * @param bool   $dismissible
     */
    public function add_elite_notice(string $message, string $type = 'info', bool $dismissible = true): void {
        \add_action('admin_notices', function() use ($message, $type, $dismissible) {
            $class = 'notice notice-' . $type;
            if ($dismissible) {
                $class .= ' is-dismissible';
            }
            
            // Modern styling override
            $style = 'border-radius: 12px; border-left-width: 6px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); padding: 15px; margin-top: 20px;';
            $colors = [
                'success' => 'border-left-color: #10b981; background: #ecfdf5;',
                'error'   => 'border-left-color: #ef4444; background: #fef2f2;',
                'warning' => 'border-left-color: #f59e0b; background: #fffbeb;',
                'info'    => 'border-left-color: #3b82f6; background: #eff6ff;',
            ];
            
            $final_style = $style . ($colors[$type] ?? '');

            printf(
                '<div class="%1$s" style="%2$s"><p style="font-weight: 600; font-size: 14px; margin: 0; color: #1e293b;">%3$s</p></div>',
                \esc_attr($class),
                \esc_attr($final_style),
                \wp_kses_post($message)
            );
        });
    }
}
