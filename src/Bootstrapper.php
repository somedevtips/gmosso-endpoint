<?php
declare (strict_types = 1);

/**
 * Bootstrap management class
 *
 * Manages the plugin bootstrap process.
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint;

use Psr\Log\NullLogger;
use Seld\JsonLint\JsonParser;

use GmossoEndpoint\Assets\AssetManager;
use GmossoEndpoint\Configuration;
use GmossoEndpoint\DataProvider\RestApiDataProvider;
use GmossoEndpoint\Endpoint\EndpointManager;
use GmossoEndpoint\Installation\Installer;
use GmossoEndpoint\Log\Logger;
use GmossoEndpoint\Mvc\Controllers;
use GmossoEndpoint\Mvc\Router;
use GmossoEndpoint\SimpleCache\TransientCache;
use GmossoEndpoint\Uninstallation\Uninstaller;
use GmossoEndpoint\Users\Controller as UserController;
use GmossoEndpoint\Users\Model as UserModel;

class Bootstrapper
{
    private string $pluginFile;
    private array $modules;
    private Configuration $configuration;

    /**
     * Class constructor.
     *
     * @since 1.0.0
     * @param string        $pluginFile    path of main plugin file.
     * @param Configuration $configuration Configuration instance.
     */
    public function __construct(string $pluginFile, Configuration $configuration)
    {
        $this->pluginFile = $pluginFile;
        $this->configuration = $configuration;
        $this->modules = [];
    }

    /**
     * Boostraps the plugin.
     *
     * Checks the php version, loads and bootstraps modules.
     *
     * @since  1.0.0
     * @return void
     */
    public function bootstrap(): bool
    {
        if (!$this->versionCheck()) {
            return false;
        }

        $this->loadModules();

        $bootstrappables = array_filter(
            $this->modules,
            function (object $module): bool {
                return $module instanceof BootstrappableInterface;
            }
        );

        foreach ($bootstrappables as $module) {
            $module->bootstrap();
        }

        return true;
    }

    /**
     * Checks that the mimimum php version is satisfied.
     *
     * @since  1.0.0
     * @return bool true if the check is successful.
     */
    protected function versionCheck(): bool
    {
        $minPhpVersion = $this->configuration['MIN_PHP_VERSION'];
        $currentPhpVersion = phpversion();
        if (version_compare($currentPhpVersion, $minPhpVersion, '<') === true) {
            $this->adminNotice(
                sprintf(
                    /* translators: 1: required PHP version (e.g. 7.4.0) 2: current PHP version */
                    __(
                        'Gmosso Endpoint requires PHP version %1$s or higher. You are running version %2$s ',
                        'gmosso-endpoint'
                    ),
                    $minPhpVersion,
                    $currentPhpVersion
                )
            );

            return false;
        }

        return true;
    }

    /**
     * Displays a notice in the WordPress backend plugin page if the minimum
     * php version is not satisfied.
     *
     * @since  1.0.0
     * @param  string $message message to display
     * @return void
     */
    protected function adminNotice(string $message): void
    {
        add_action(
            'admin_notices',
            function () use ($message) {
                $class = 'notice notice-error';
                printf(
                    '<div class="%1$s"><p>%2$s</p></div>',
                    esc_attr($class),
                    esc_html($message)
                );
            }
        );
    }

    /**
     * Loads all plugin modules.
     *
     * @since  1.0.0
     * @return void
     */
    protected function loadModules(): void
    {
        $logger = $this->configuration['DEBUG'] ? new Logger() : new NullLogger();

        $this->modules[Logger::class] = $logger;

        $this->modules[TransientCache::class] = new TransientCache();

        $this->modules[RestApiDataProvider::class] = new RestApiDataProvider(
            $this->modules[Logger::class],
            new JsonParser(),
            $this->configuration
        );

        $this->modules[UserModel::class] = new UserModel(
            $this->modules[RestApiDataProvider::class],
            $this->modules[TransientCache::class],
            $this->configuration
        );

        $this->modules[UserController::class] = new UserController(
            $this->modules[UserModel::class],
            dirname($this->pluginFile),
            $this->configuration
        );

        $this->modules[Controllers::class] = new Controllers();
        $this->modules[Controllers::class]['users'] =
            $this->modules[UserController::class];

        $this->modules[Router::class] = new Router(
            $this->modules[Controllers::class],
            $this->configuration
        );

        $this->modules[EndpointManager::class] = new EndpointManager(
            $this->configuration
        );

        $this->modules[AssetManager::class] = new AssetManager(
            $this->pluginFile,
            $this->modules[Router::class],
            $this->configuration
        );

        $this->modules[Uninstaller::class] = new Uninstaller(
            $this->modules[TransientCache::class],
            $this->configuration
        );

        $this->modules[Installer::class] = new Installer(
            [$this->modules[UserModel::class]],
            $this->pluginFile,
            $this->modules[Uninstaller::class],
            $this->configuration
        );
    }
}
