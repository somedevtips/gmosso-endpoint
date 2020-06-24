<?php
declare (strict_types = 1);

namespace GmossoEndpoint\Tests\Assets;

use GmossoEndpoint\Assets\AssetManager;
use GmossoEndpoint\Configuration;
use GmossoEndpoint\Mvc\Router;
use GmossoEndpoint\Tests\GmossoEndpointTestCase;

use Brain\Monkey\Functions;
use Mockery;

class AssetManagerTest extends GmossoEndpointTestCase
{
    protected Configuration $configuration;
    protected AssetManager $assetManager;
    protected string $pluginFile;
    protected Router $router;

    public const MOCKED_PLUGIN_PREFIX_VALUE = 'mocked_plugin_prefix';

    protected function setUp(): void
    {
        parent::setUp();

        $this->configuration = Mockery::mock(
            'GmossoEndpoint\Configuration'
        );
        $this->configuration->shouldReceive('offsetGet')
            ->with('PLUGIN_PREFIX')
            ->andReturn(self::MOCKED_PLUGIN_PREFIX_VALUE)
            ->byDefault();

        $this->router = Mockery::mock(
            'GmossoEndpoint\Mvc\Router'
        );

        $this->assetManager = new AssetManager(
            __DIR__ . '/../../gmosso-endpoint.php',
            $this->router,
            $this->configuration
        );
    }

    public function testBootstrapAllHooks()
    {
        $this->assetManager->bootstrap();

        $this->assertTrue(
            has_action(
                'wp_enqueue_scripts',
                'GmossoEndpoint\Assets\AssetManager->enqueueFrontEndScripts()'
            )
        );

        $this->assertTrue(
            has_action(
                'wp_enqueue_scripts',
                'GmossoEndpoint\Assets\AssetManager->enqueueFrontEndStyles()'
            )
        );
    }

    public function testEnqueueFrontEndScripts()
    {
        $numScripts = 1;
        $numLocalizedScripts = 1;

        Functions\expect('wp_enqueue_script')
            ->times($numScripts);

        Functions\expect('wp_localize_script')
            ->times($numLocalizedScripts);

        Functions\expect('plugins_url')
            ->once();

        Functions\expect('admin_url')
            ->once();

        $this->router->shouldReceive('ajaxActionGetItem')
            ->once()
            ->withNoArgs();

        $this->assetManager->enqueueFrontEndScripts();
    }

    public function testEnqueueFrontEndStyles()
    {
        $numStyles = 1;

        Functions\expect('wp_enqueue_style')
            ->times($numStyles);

        Functions\expect('plugins_url')
            ->once();

        $this->assetManager->enqueueFrontEndStyles();
    }
}
