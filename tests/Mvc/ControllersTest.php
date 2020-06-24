<?php
declare (strict_types = 1);

namespace GmossoEndpoint\Tests\Mvc;

use GmossoEndpoint\Mvc\Controllers;
use GmossoEndpoint\Users\Controller;
use GmossoEndpoint\Users\Model;
use GmossoEndpoint\Tests\GmossoEndpointTestCase;

use Brain\Monkey\Functions;
use Mockery;

class ControllersTest extends GmossoEndpointTestCase
{
    protected Controllers $controllers;
    protected Controller $controllerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->controllerMock =
            Mockery::mock('\GmossoEndpoint\Users\Controller');

        $this->controllers = new Controllers();

        Functions\stubTranslationFunctions();
    }

    public function testNotStringKeyThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->controllers[1] = $this->controllerMock;
    }

    public function testNotAbstractControllerValueThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->controllers['mykey'] = new \stdClass();
    }

    public function testStoreAndRetrieveControllerOK()
    {
        $key = 'mykey';
        $this->controllers[$key] = $this->controllerMock;

        $storedController = $this->controllers[$key];

        $this->assertSame($this->controllerMock, $storedController);
    }

    public function testKeyExists()
    {
        $key = 'mykey';
        $this->controllers[$key] = $this->controllerMock;

        $this->assertTrue(isset($this->controllers[$key]));
    }

    public function testKeyDoesNotExist()
    {
        $key = 'mykey';
        $this->controllers[$key] = $this->controllerMock;

        $this->assertFalse(isset($this->controllers[$key . 'a']));
    }

    public function testGetNotExistingKeyThrowsException()
    {
        $key = 'mykey';
        $this->controllers[$key] = $this->controllerMock;

        $this->expectException(\OutOfRangeException::class);

        $value = $this->controllers[$key . 'a'];
    }

    public function testUnsetController()
    {
        $key = 'mykey';
        $this->controllers[$key] = $this->controllerMock;

        $this->assertTrue(isset($this->controllers[$key]));

        unset($this->controllers[$key]);

        $this->assertFalse(isset($this->controllers[$key]));
    }

    public function testUnsetNotExistingKeyThrowsException()
    {
        $key = 'mykey';
        $this->controllers[$key] = $this->controllerMock;

        $this->expectException(\OutOfRangeException::class);

        unset($this->controllers[$key . 'a']);
    }

    public function testFirstControllerFound()
    {
        $key = 'mykey';
        $this->controllers[$key] = $this->controllerMock;

        $storedController = $this->controllers->firstController();

        $this->assertSame($this->controllerMock, $storedController);
    }

    public function testFirstControllerNullIfControllersIsEmpty()
    {
        $storedController = $this->controllers->firstController();
        $this->assertNull($storedController);
    }
}
