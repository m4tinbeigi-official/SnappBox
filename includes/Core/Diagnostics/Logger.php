<?php
declare(strict_types=1);

namespace Snappbox\Core\Diagnostics;

defined('ABSPATH') || exit;

/**
 * Enterprise Diagnostic Logger for SnappBox Elite.
 * Follows PSR-3 patterns for reliability and maintenance.
 */
class Logger {

    /**
     * Log a message to the WooCommerce system log.
     *
     * @param string $message
     * @param string $level 'info', 'error', 'debug', 'warning'
     * @param array  $context Additional data to log
     */
    public function log(string $message, string $level = 'info', array $context = []): void {
        if (!\function_exists('wc_get_logger')) {
            return;
        }

        $logger = \wc_get_logger();
        $handle = 'snappbox-elite';
        
        $full_message = $message;
        if (!empty($context)) {
            $full_message .= ' | Context: ' . \wp_json_encode($context);
        }

        $logger->log($level, $full_message, ['source' => $handle]);
    }

    public function error(string $message, array $context = []): void {
        $this->log($message, 'error', $context);
    }

    public function info(string $message, array $context = []): void {
        $this->log($message, 'info', $context);
    }

    public function debug(string $message, array $context = []): void {
        $this->log($message, 'debug', $context);
    }
}
