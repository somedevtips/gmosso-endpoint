<?php
declare (strict_types = 1);

namespace GmossoEndpoint\Tests;

use GmossoEndpoint\Configuration;
use GmossoEndpoint\Tests\GmossoEndpointTestCase;

use Brain\Monkey\Functions;
use Mockery;

class ConfigurationTest extends GmossoEndpointTestCase
{
    protected Configuration $configuration;
    public const EXISTING_KEY = 'PLUGIN_PREFIX';
    public const VALUE_FOR_EXISTING_KEY = 'gmosso_endpoint';
    public const NOT_EXISTING_KEY = 'DUMMY_NOT_EXISTING';

    protected function setUp(): void
    {
        parent::setUp();

        $this->configuration = new Configuration();

        Functions\stubTranslationFunctions();
    }

    public function testSetValueThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->configuration['API_ROOT'] = 'https://newapi.com';
    }

    public function testUnsetValueThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        unset($this->configuration['API_ROOT']);
    }

    public function testIssetOnExistingKeyReturnsTrue()
    {
        $this->assertTrue(isset($this->configuration[self::EXISTING_KEY]));
    }

    public function testIssetOnNotExistingKeyReturnsFalse()
    {
        $this->assertFalse(isset($this->configuration[self::NOT_EXISTING_KEY]));
    }

    public function testGetNotExistingKeyThrowsException()
    {
        $this->expectException(\OutOfRangeException::class);

        $value = $this->configuration[self::NOT_EXISTING_KEY];
    }

    public function testGetExistingKeyReturnsCorrectValue()
    {
        $value = $this->configuration[self::EXISTING_KEY];

        $this->assertSame(self::VALUE_FOR_EXISTING_KEY, $value);
    }
}
