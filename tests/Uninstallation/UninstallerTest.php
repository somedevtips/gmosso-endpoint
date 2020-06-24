<?php
declare (strict_types = 1);

namespace GmossoEndpoint\Tests\Uninstallation;

use GmossoEndpoint\Tests\GmossoEndpointTestCase;

use GmossoEndpoint\Configuration;
use GmossoEndpoint\SimpleCache\CacheInterface;
use GmossoEndpoint\Uninstallation\Uninstaller;

use Mockery;
use Brain\Monkey\Functions;

class UninstallerTest extends GmossoEndpointTestCase
{
    protected Uninstaller $uninstaller;
    protected CacheInterface $cache;
    protected Configuration $configuration;

    public const MOCKED_OPTION_NAME_VALUE = 'mocked_option_name';
    public const MOCKED_OPTION_TRANSIENTS_VALUE = 'mocked_transients';

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache =
            Mockery::mock('\GmossoEndpoint\SimpleCache\CacheInterface');

        $this->configuration = Mockery::mock(
            'GmossoEndpoint\Configuration'
        );
        $this->configuration->shouldReceive('offsetGet')
            ->with('OPTION_NAME')
            ->andReturn(self::MOCKED_OPTION_NAME_VALUE)
            ->byDefault();

        $this->uninstaller = new Uninstaller($this->cache, $this->configuration);

        Functions\expect('delete_option')
            ->once()
            ->with(self::MOCKED_OPTION_NAME_VALUE);

        Functions\expect('flush_rewrite_rules')
            ->once()
            ->withNoArgs();

        Functions\expect('wp_cache_flush')
            ->once()
            ->withNoArgs();
    }

    public function testUninstallCompleteIfOptionsContainTransients()
    {
        $pluginOptions = [
            self::MOCKED_OPTION_TRANSIENTS_VALUE => ['transient1', 'transient2'],
        ];

        Functions\expect('get_option')
            ->once()
            ->with(self::MOCKED_OPTION_NAME_VALUE, false)
            ->andReturn($pluginOptions);

        $this->configuration->shouldReceive('offsetGet')
            ->with('OPTION_TRANSIENTS')
            ->andReturn(self::MOCKED_OPTION_TRANSIENTS_VALUE);

        $this->cache->shouldReceive('delete')
            ->times(count($pluginOptions[self::MOCKED_OPTION_TRANSIENTS_VALUE]))
            ->withArgs(function (string $argument) use ($pluginOptions): bool {
                return in_array(
                    $argument,
                    $pluginOptions[self::MOCKED_OPTION_TRANSIENTS_VALUE],
                    true
                );
            });

        $this->uninstaller->uninstall();
    }

    public function testUninstallDoesNotDeleteTransientsIfTheyAreNotInOptions()
    {
        $pluginOptions = [];

        Functions\expect('get_option')
            ->once()
            ->with(self::MOCKED_OPTION_NAME_VALUE, false)
            ->andReturn($pluginOptions);

        $this->configuration->shouldReceive('offsetGet')
            ->with('OPTION_TRANSIENTS')
            ->andReturn(self::MOCKED_OPTION_TRANSIENTS_VALUE);

        $this->cache->shouldReceive('delete')
            ->never();

        $this->uninstaller->uninstall();
    }

    public function testUninstallDoesNotDeleteTransientsIfNoOptionIsStored()
    {
        Functions\expect('get_option')
            ->once()
            ->with(self::MOCKED_OPTION_NAME_VALUE, false)
            ->andReturn(false);

        $this->cache->shouldReceive('delete')
            ->never();

        $this->uninstaller->uninstall();
    }
}
