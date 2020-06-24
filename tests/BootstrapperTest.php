<?php
declare (strict_types = 1);

namespace GmossoEndpoint\Tests;

use GmossoEndpoint\Tests\GmossoEndpointTestCase;

use GmossoEndpoint\Bootstrapper;
use GmossoEndpoint\Configuration;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use Mockery;

class BootstrapperTest extends GmossoEndpointTestCase
{
    protected Bootstrapper $bootstrapper;
    protected Configuration $configuration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configuration = Mockery::mock(
            'GmossoEndpoint\Configuration'
        );

        //Create partial mock to allow mocking loadModules when needed
        $this->bootstrapper = Mockery::mock(
            '\GmossoEndpoint\Bootstrapper',
            [__FILE__, $this->configuration]
        )
            ->makePartial();

        Functions\stubTranslationFunctions();
    }

    public function testBootstrapDisplaysNoticeIfMinPhpVersionNotSatisfied()
    {
        $this->configuration->shouldReceive('offsetGet')
            ->with('MIN_PHP_VERSION')
            ->andReturn('20.0.0');

        Actions\expectAdded('admin_notices')->with(Mockery::type('Closure'));

        $this->bootstrapper
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('loadModules')
            ->never();

        $this->assertFalse($this->bootstrapper->bootstrap());
    }

    public function testBootstrapReturnsTrueIfMinPhpVersionIsSatisfied()
    {
        $this->configuration->shouldReceive('offsetGet')
            ->with('MIN_PHP_VERSION')
            ->andReturn('1.0.0');

        $this->bootstrapper
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('loadModules')
            ->once()
            ->withNoArgs();

        $this->assertTrue($this->bootstrapper->bootstrap());
    }

    /**
     * Needed to avoid 'Could not load mock ... class already exists' error
     * when running with coverage
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testBootstrapComplete()
    {
        $this->configuration->shouldReceive('offsetGet')
            ->with('MIN_PHP_VERSION')
            ->andReturn('1.0.0');

        $this->configuration->shouldReceive('offsetGet')
            ->with('DEBUG')
            ->andReturn(1);

        $modules = $this->modulesInstantiatedExceptControllers();
        $bootstrappables = $this->modulesBootstrappables();
        foreach ($modules as $module) {
            if (in_array($module, $bootstrappables, true)) {
                Mockery::mock(
                    'overload:' . $module,
                    'GmossoEndpoint\BootstrappableInterface'
                )
                    ->shouldReceive('bootstrap')
                    ->once()
                    ->withNoArgs();

                continue;
            }

            Mockery::mock('overload:' . $module);
        }
        $controllersMock = Mockery::mock(
            'overload:GmossoEndpoint\Mvc\Controllers',
            '\ArrayAccess'
        );
        $controllersMock->shouldReceive('offsetSet')
            ->once();

        $this->assertTrue($this->bootstrapper->bootstrap());
    }

    protected function modulesInstantiatedExceptControllers(): array
    {
        return [
            'GmossoEndpoint\Assets\AssetManager',
            'GmossoEndpoint\DataProvider\RestApiDataProvider',
            'GmossoEndpoint\Endpoint\EndpointManager',
            'GmossoEndpoint\Installation\Installer',
            'GmossoEndpoint\Log\Logger',
            'GmossoEndpoint\Mvc\Router',
            'GmossoEndpoint\SimpleCache\TransientCache',
            'GmossoEndpoint\Uninstallation\Uninstaller',
            'GmossoEndpoint\Users\Controller',
            'GmossoEndpoint\Users\Model',
        ];
    }

    protected function modulesBootstrappables(): array
    {
        return [
            'GmossoEndpoint\Assets\AssetManager',
            'GmossoEndpoint\Endpoint\EndpointManager',
            'GmossoEndpoint\Installation\Installer',
            'GmossoEndpoint\Mvc\Router',
            'GmossoEndpoint\Users\Controller',
        ];
    }
}
