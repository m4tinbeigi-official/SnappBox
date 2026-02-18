<?php
declare(strict_types=1);

namespace Snappbox\Core\Diagnostics;

defined('ABSPATH') || exit;

/**
 * Professional Health Check and Diagnostics for SnappBox.
 */
class HealthCheck {

    /**
     * Perform a quick system-wide health check.
     *
     * @return array Health status report
     */
    public function perform_check(): array {
        $status = [
            'api_connected' => $this->check_api_connectivity(),
            'php_version'   => \PHP_VERSION,
            'is_ssl'        => \is_ssl(),
            'timezone'      => \wp_timezone_string(),
        ];

        return $status;
    }

    private function check_api_connectivity(): bool {
        $response = \wp_remote_get('https://api.snapp-box.com/health');
        return !\is_wp_error($response) && \wp_remote_retrieve_response_code($response) === 200;
    }
}
