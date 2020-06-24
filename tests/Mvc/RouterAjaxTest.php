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

class RouterAjaxTest extends GmossoEndpointTestCase
{
    protected Router $router;
    protected Controllers $controllers;
    protected Controller $userController;
    protected Configuration $configuration;
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

        //Create partial mock to allow mocking inputData
        $this->router = Mockery::mock(
            '\GmossoEndpoint\Mvc\Router, \GmossoEndpoint\BootstrappableInterface',
            [$this->controllers, $this->configuration]
        )
            ->makePartial();

        Functions\stubTranslationFunctions();
    }

    public function testRouteAjaxSendsErrorIfEndpointDoesNotExist()
    {
        $this->router->shouldAllowMockingProtectedMethods()
            ->shouldReceive('inputData')
            ->once()
            ->withNoArgs()
            ->andReturn([1, 'notexisting']);

        $this->configuration->shouldReceive('offsetGet')
            ->with('ENDPOINTS')
            ->andReturn(self::MOCKED_ENDPOINTS_VALUE);

        $this->controllers->shouldReceive('firstController')
            ->withNoArgs()
            ->andReturn($this->userController);

        $this->userController->shouldReceive('outputError')
            ->once()
            ->with(Mockery::type('string'));

        $this->router->routeAjax();
    }

    public function testRouteAjaxSendsErrorIfItemIdNotInteger()
    {
        $firstEndpointKey = array_key_first(self::MOCKED_ENDPOINTS_VALUE);

        $this->router->shouldAllowMockingProtectedMethods()
            ->shouldReceive('inputData')
            ->once()
            ->withNoArgs()
            ->andReturn([false, $firstEndpointKey]);

        $this->configuration->shouldReceive('offsetGet')
            ->with('ENDPOINTS')
            ->andReturn(self::MOCKED_ENDPOINTS_VALUE);

        $this->controllers->shouldReceive('offsetGet')
            ->with($firstEndpointKey)
            ->andReturn($this->userController);

        $this->userController->shouldReceive('outputError')
            ->once()
            ->with(Mockery::type('string'));

        $this->router->routeAjax();
    }

    public function testRouteAjaxCorrect()
    {
        $itemId = 1;
        $firstEndpointKey = array_key_first(self::MOCKED_ENDPOINTS_VALUE);

        $this->router->shouldAllowMockingProtectedMethods()
            ->shouldReceive('inputData')
            ->once()
            ->withNoArgs()
            ->andReturn([$itemId, $firstEndpointKey]);

        $this->configuration->shouldReceive('offsetGet')
            ->with('ENDPOINTS')
            ->andReturn(self::MOCKED_ENDPOINTS_VALUE);

        $this->controllers->shouldReceive('offsetGet')
            ->with($firstEndpointKey)
            ->andReturn($this->userController);

        $this->userController->shouldReceive('outputSingleItem')
            ->once()
            ->with($itemId);

        $this->router->routeAjax();
    }
}
