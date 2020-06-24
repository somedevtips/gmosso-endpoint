<?php
declare (strict_types = 1);

/**
 * Plugin Name:       Gmosso Endpoint
 * Plugin URI:        https://giancarlomosso@bitbucket.org/giancarlomosso/gmosso-endpoint.git
 * Description:       Test plugin for a new endpoint
 * Version:           1.0.0
 * Author:            Giancarlo Mosso
 * Author URI:        https://somedevtips.com/
 * License:           GPL-3.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       gmosso-endpoint
 * Domain Path:       /languages
 */

namespace GmossoEndpoint;

use GmossoEndpoint\Configuration;

defined('ABSPATH') or die;

$gmossoEndpointAutoloader = __DIR__ . '/vendor/autoload.php';
if (!file_exists($gmossoEndpointAutoloader)) {
    return false;
}
require $gmossoEndpointAutoloader;

$gmossoEndpointConfiguration = new Configuration();

$gmossoEndpointBootstrapper = new Bootstrapper(
    __FILE__,
    $gmossoEndpointConfiguration
);
add_action('plugins_loaded', [$gmossoEndpointBootstrapper, 'bootstrap'], 0);

add_action('init', function () {
    load_plugin_textdomain(
        'gmosso-endpoint',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
});

register_deactivation_hook(__FILE__, function () use ($gmossoEndpointConfiguration) {
    //Remove endpoint and flush rules
    add_filter('root_rewrite_rules', function (
        array $rules
    ) use ($gmossoEndpointConfiguration): array {
        $prefix = $gmossoEndpointConfiguration['ENDPOINT_PREFIX'];
        $endpoints = array_keys($gmossoEndpointConfiguration['ENDPOINTS']);

        $filteredRules = array_filter(
            $rules,
            function (string $key) use ($prefix, $endpoints): bool {
                foreach ($endpoints as $endpoint) {
                    if (strpos($key, $prefix . $endpoint) === 0) {
                        return false;
                    }
                }
                return true;
            },
            ARRAY_FILTER_USE_KEY
        );
        return $filteredRules;
    });
    flush_rewrite_rules();

    //Reset installed option to 0 so that, if plugin is activated again,
    //the endpoint will be added again. We don't delete transients
    //because if plugin is activated again soon they could still be valid.
    //Transients are deleted on uninstall
    $pluginOptions = get_option($gmossoEndpointConfiguration['OPTION_NAME'], false);
    if ($pluginOptions !== false) {
        $pluginOptions[$gmossoEndpointConfiguration['OPTION_INSTALLED']] = 0;
        update_option($gmossoEndpointConfiguration['OPTION_NAME'], $pluginOptions);
    }
});
