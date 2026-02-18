<?php
declare(strict_types=1);
/**
 * PSR-4 Autoloader for SnappBox Plugin
 *
 * @package Snappbox
 */

spl_autoload_register(function ($class) {
    $prefix = 'Snappbox\\';
    $base_dir = SNAPPBOX_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});
