<?php
declare(strict_types=1);

/**
 * Management of plugin install and upgrade
 *
 * Executes the actions needed to install and upgrade this plugin.
 *
 * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\Installation;

use GmossoEndpoint\BootstrappableInterface;
use GmossoEndpoint\Configuration;
use GmossoEndpoint\Uninstallation\Uninstaller;

class Installer implements BootstrappableInterface
{
    private array $models;
    private string $pluginFile;
    private Uninstaller $uninstaller;
    private Configuration $configuration;

    /**
     * Class constructor.
     *
     * @since 1.0.0
     * @param array         $models        array of all the models. Every model
     *                                     must extend the Mvc\AbstractModel class
     * @param string        $pluginFile    main file of this plugin
     * @param Uninstaller   $uninstaller   Uninstaller instance
     * @param Configuration $configuration Configuration instance
     */
    public function __construct(
        array $models,
        string $pluginFile,
        Uninstaller $uninstaller,
        Configuration $configuration
    ) {

        $this->models = $models;
        $this->pluginFile = $pluginFile;
        $this->uninstaller = $uninstaller;
        $this->configuration = $configuration;
    }

    /**
     * Sets callbacks for hooks at plugin bootstrap.
     *
     * @since  1.0.0
     * @return void
     */
    public function bootstrap(): void
    {
        add_action('wp_loaded', [$this, 'afterInstall']);
        add_action('upgrader_process_complete', [$this, 'manageUpgrade'], 10, 2);
    }

    /**
     * Operations to execute after activating or upgrading the plugin.
     *
     * @since  1.0.0
     * @return void
     */
    public function afterInstall(): void
    {
        $pluginOptions = get_option($this->configuration['OPTION_NAME'], false);

        if ($pluginOptions !== false &&
            is_int($pluginOptions[$this->configuration['OPTION_INSTALLED']]) &&
            intval($pluginOptions[$this->configuration['OPTION_INSTALLED']]) === 1) {
            return;
        }

        flush_rewrite_rules();

        // Write options
        $transients = [];
        foreach ($this->models as $model) {
            $transients[] = $model->transientKey();
        }
        $pluginOptions = [
            $this->configuration['OPTION_INSTALLED'] => 1,
            $this->configuration['OPTION_TRANSIENTS'] => $transients,
        ];
        update_option($this->configuration['OPTION_NAME'], $pluginOptions);
    }

    /**
     * Operations to execute only after upgrading the plugin.
     *
     * @since  1.0.0
     * @param  object $upgrader WP_Upgrader instance
     * @param  array  $options  Options used by the WP_Upgrader.
     * @return void
     */
    public function manageUpgrade(object $upgrader, array $options): void
    {
        $thisPlugin = plugin_basename($this->pluginFile);

        // Check that this plugin is updated
        if ($options['action'] === 'update'
            && $options['type'] === 'plugin'
            && isset($options['plugins'])
        ) {
            if (!in_array($thisPlugin, $options['plugins'], true)) {
                return;
            }

            $pluginOptions = get_option($this->configuration['OPTION_NAME'], false);
            if ($pluginOptions === false) {
                return;
            }

            //Reset installed status to force updating rewrite rules
            //in case new rules have been added
            $pluginOptions[$this->configuration['OPTION_INSTALLED']] = 0;
            update_option($this->configuration['OPTION_NAME'], $pluginOptions);

            //Remove transients because in new version data could be
            //fetched from a different source or managed differently
            $this->uninstaller->deleteTransients();
        }
    }
}
