<?php
declare(strict_types=1);

/**
 * Plugin uninstalling logic
 *
 * Actions to execute when uninstalling the plugin.
 *
 * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\Uninstallation;

use GmossoEndpoint\Configuration;
use GmossoEndpoint\SimpleCache\CacheInterface;

class Uninstaller
{
    private CacheInterface $cache;
    private Configuration $configuration;

    /**
     * Class constructor.
     *
     * @since 1.0.0
     * @param CacheInterface $cache         instance of the cache
     * @param Configuration  $configuration Configuration instance
     */
    public function __construct(
        CacheInterface $cache,
        Configuration $configuration
    ) {

        $this->cache = $cache;
        $this->configuration = $configuration;
    }

    /**
     * Main uninstall routine called by uninstaller.php
     *
     * @since  1.0.0
     * @return void
     */
    public function uninstall(): void
    {
        $this->deleteTransients();
        $this->deleteOptions();
        wp_cache_flush();
        flush_rewrite_rules();
    }

    /**
     * Deletes all transients.
     *
     * @since  1.0.0
     * @return void
     */
    public function deleteTransients(): void
    {
        $pluginOptions = get_option($this->configuration['OPTION_NAME'], false);

        if ($pluginOptions === false) {
            return;
        }

        $transientKeys = $pluginOptions[$this->configuration['OPTION_TRANSIENTS']] ??= [];
        foreach ($transientKeys as $transientKey) {
            $this->cache->delete($transientKey);
        }
    }

    /**
     * Deletes all options.
     *
     * @since  1.0.0
     * @return void
     */
    protected function deleteOptions(): void
    {
        delete_option($this->configuration['OPTION_NAME']);
    }
}
