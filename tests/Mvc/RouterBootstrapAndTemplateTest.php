<?php
declare (strict_types = 1);

namespace GmossoEndpoint\Tests\Mvc;

use GmossoEndpoint\Configuration;
use GmossoEndpoint\Mvc\Controllers;
use GmossoEndpoint\Mvc\Router;
use GmossoEndpoint\Tests\GmossoEndpointTestCase;
use GmossoEndpoint\Users\Controller;

use Brain\Monkey\Functions;
use Mockery;

class RouterBootstrapAndTemplateTest extends GmossoEndpointTestCase
{
    protected Router $router;
    protected Configuration $configuration;
    protected Controllers $controllers;
    protected Controller $userController;

    public const MOCKED_PLUGIN_PREFIX_VALUE = 'mocked_plugin_prefix';
    public const MOCKED_ENDPOINT_PREFIX_VALUE = 'mocked_endpoint_prefix';
    public const MOCKED_ENDPOINTS_VALUE = [
        'endpoint1' => ['template' => 'template1.php'],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->controllers = Mockery::mock('\GmossoEndpoint\Mvc\Controllers');
        $this->controllers->shouldReceive('offsetSet')->byDefault();

        $this->userController =
            Mockery::mock('\GmossoEndpoint\Users\Controller');
        $this->controllers[array_key_first(self::MOCKED_ENDPOINTS_VALUE)] =
            $this->userController;

        $this->configuration = Mockery::mock(
            'GmossoEndpoint\Configuration'
        );

        $this->router = new Router($this->controllers, $this->configuration);
    }

    public function testBootstrapAllHooks()
    {
        $this->configuration->shouldReceive('offsetGet')
            ->with('PLUGIN_PREFIX')
            ->andReturn(self::MOCKED_PLUGIN_PREFIX_VALUE);

        $ajaxActionGetItem = $this->router->ajaxActionGetItem();

        $this->router->bootstrap();

        $this->assertTrue(
            has_filter(
                'template_include',
                '\GmossoEndpoint\Mvc\Router->route()'
            )
        );

        $this->assertTrue(
            has_action(
                'wp_ajax_' . $ajaxActionGetItem,
                '\GmossoEndpoint\Mvc\Router->routeAjax()'
            )
        );

        $this->assertTrue(
            has_action(
                'wp_ajax_nopriv_' . $ajaxActionGetItem,
                '\GmossoEndpoint\Mvc\Router->routeAjax()'
            )
        );
    }

    public function testEndpointTemplateIsIncludedIfEndpointIsMatched()
    {
        $firstEndpointKey = array_key_first(self::MOCKED_ENDPOINTS_VALUE);

        $this->configuration->shouldReceive('offsetGet')
            ->with('ENDPOINT_PREFIX')
            ->andReturn(self::MOCKED_ENDPOINT_PREFIX_VALUE);

        $this->configuration->shouldReceive('offsetGet')
            ->with('ENDPOINTS')
            ->andReturn(self::MOCKED_ENDPOINTS_VALUE);

        $endpoint = self::MOCKED_ENDPOINT_PREFIX_VALUE . $firstEndpointKey;
        $originalTemplate = 'my_template.php';
        $endpointTemplate = self::MOCKED_ENDPOINTS_VALUE[$firstEndpointKey]['template'];

        Functions\expect('get_query_var')
            ->once()
            ->with($endpoint, false)
            ->andReturn('');

        $this->controllers->shouldReceive('offsetGet')
            ->with($firstEndpointKey)
            ->andReturn($this->userController);

        $this->userController->shouldReceive('allItemsTemplate')
            ->once()
            ->withNoArgs()
            ->andReturn($endpointTemplate);

        $template = $this->router->route($originalTemplate);

        $this->assertSame($endpointTemplate, $template);
    }

    public function testTemplateDoesNotChangeIfEndpointIsNotMatched()
    {
        $firstEndpointKey = array_key_first(self::MOCKED_ENDPOINTS_VALUE);

        $this->configuration->shouldReceive('offsetGet')
            ->with('ENDPOINT_PREFIX')
            ->andReturn(self::MOCKED_ENDPOINT_PREFIX_VALUE);

        $this->configuration->shouldReceive('offsetGet')
            ->with('ENDPOINTS')
            ->andReturn(self::MOCKED_ENDPOINTS_VALUE);

        $endpoint = self::MOCKED_ENDPOINT_PREFIX_VALUE . $firstEndpointKey;
        $originalTemplate = 'my_template.php';

        Functions\expect('get_query_var')
            ->once()
            ->with($endpoint, false)
            ->andReturn(false);

        $this->controllers->shouldReceive('offsetGet')
            ->never();

        $template = $this->router->route($originalTemplate);

        $this->assertSame($originalTemplate, $template);
    }
}
