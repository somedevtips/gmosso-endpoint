<?php
declare (strict_types = 1);

/**
 * Management of assets
 *
 * Enqueues script and style files.
 *
 * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\Assets;

use GmossoEndpoint\BootstrappableInterface;
use GmossoEndpoint\Configuration;
use GmossoEndpoint\Mvc\Router;

class AssetManager implements BootstrappableInterface
{
    private string $pluginFile;
    private Router $router;
    private Configuration $configuration;
    private string $prefix;
    private string $pluginDir;

    /**
     * Class constructor.
     *
     * @since 1.0.0
     * @param string $pluginFile plugin main file path
     * @param Router $router instance of the Router class
     * @param Configuration $configuration instance of teh Configuration class
     */
    public function __construct(
        string $pluginFile,
        Router $router,
        Configuration $configuration
    ) {

        $this->pluginFile = $pluginFile;
        $this->pluginDir = dirname($pluginFile);
        $this->router = $router;
        $this->configuration = $configuration;
        $this->prefix = str_replace('_', '-', $this->configuration['PLUGIN_PREFIX']);
    }

    /**
     * Enqueues scripts and styles at plugin bootstrap.
     *
     * @since  1.0.0
     * @return void
     */
    public function bootstrap(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontEndScripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontEndStyles']);
    }

    /**
     * Enqueues front end script files.
     *
     * @since  1.0.0
     * @return void
     */
    public function enqueueFrontEndScripts(): void
    {
        wp_enqueue_script(
            "{$this->prefix}-users",
            plugins_url('/public/js/users.min.js', $this->pluginFile),
            ['jquery'],
            filemtime($this->pluginDir . '/public/js/users.min.js'),
            true
        );

        wp_localize_script(
            "{$this->prefix}-users",
            $this->configuration['PLUGIN_PREFIX'] . '_config_data',
            [
                'action' => $this->router->ajaxActionGetItem(),
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'endpoint' => 'users',
            ]
        );
    }

    /**
     * Enqueues front end style files.
     *
     * @since  1.0.0
     * @return void
     */
    public function enqueueFrontEndStyles(): void
    {
        wp_enqueue_style(
            "{$this->prefix}-front-style",
            plugins_url('/public/css/front.min.css', $this->pluginFile),
            [],
            filemtime($this->pluginDir . '/public/css/front.min.css'),
            'screen'
        );
    }
}
