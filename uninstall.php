<?php
declare (strict_types = 1);

/**
 * Plugin uninstall management
 *
 * Code executed when the plugin is uninstalled from the WordPress plugin page.
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint;

use GmossoEndpoint\Configuration;
use GmossoEndpoint\SimpleCache\TransientCache;
use GmossoEndpoint\Uninstallation\Uninstaller;

defined('WP_UNINSTALL_PLUGIN') or die;

$autoload = __DIR__ . '/vendor/autoload.php';

if (file_exists($autoload)) {
    require $autoload;
}

$uninstaller = new Uninstaller(
    new TransientCache(),
    new Configuration()
);
$uninstaller->uninstall();
