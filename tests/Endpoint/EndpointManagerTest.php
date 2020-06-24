<?php
declare (strict_types = 1);

namespace GmossoEndpoint\Tests\Endpoint;

use GmossoEndpoint\Configuration;
use GmossoEndpoint\Endpoint\EndpointManager;
use GmossoEndpoint\Tests\GmossoEndpointTestCase;

use Brain\Monkey\Functions;
use Mockery;

/**
 * @backupGlobals enabled
 */
class EndpointManagerTest extends GmossoEndpointTestCase
{
    protected Configuration $configuration;
    protected EndpointManager $endpointManager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configuration = Mockery::mock(
            'GmossoEndpoint\Configuration'
        );

        $this->endpointManager = new EndpointManager($this->configuration);
    }

    public function testAddEndpoints()
    {
        //Give a different value than WP value to verify that this
        //constant is passed as second argument
        define('EP_ROOT', 1);

        $prefix = 'prefix';
        $endpoints = [
            'endpoint1' => ['template' => 'template1.php'],
            'endpoint2' => ['template' => 'template2.php'],
        ];

        $this->configuration->shouldReceive('offsetGet')
            ->with('ENDPOINT_PREFIX')
            ->andReturn($prefix);

        $this->configuration->shouldReceive('offsetGet')
            ->with('ENDPOINTS')
            ->andReturn($endpoints);

        $numEndpoints = count($endpoints);

        Functions\expect('add_rewrite_endpoint')
            ->times($numEndpoints)
            ->with(
                Mockery::on(function (string $argument) use ($prefix, $endpoints): bool {
                    //Check argument is in the endpoints list
                    $endpointNames = array_keys($endpoints);

                    if (strpos($argument, $prefix) !== 0) {
                        return false;
                    }

                    if (!in_array(
                        str_replace(
                            $prefix,
                            '',
                            $argument
                        ),
                        $endpointNames,
                        true
                    )) {
                        return false;
                    }
                    return true;
                }),
                EP_ROOT
            );
        $this->endpointManager->addEndpoints();
    }

    /**
     * @backupGlobals disabled
     */
    public function testBootstrapAllHooks()
    {
        $this->endpointManager->bootstrap();
        $this->assertTrue(
            has_action(
                'init',
                'GmossoEndpoint\Endpoint\EndpointManager->addEndpoints()'
            )
        );
    }
}
