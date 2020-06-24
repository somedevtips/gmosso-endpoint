<?php
declare (strict_types = 1);

namespace GmossoEndpoint\Tests\SimpleCache;

use GmossoEndpoint\SimpleCache\InvalidArgumentException;
use GmossoEndpoint\SimpleCache\TransientCache;
use GmossoEndpoint\Tests\GmossoEndpointTestCase;

use Brain\Monkey\Functions;

class TransientCacheGetTest extends GmossoEndpointTestCase
{
    protected TransientCache $cache;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cache = new TransientCache();

        Functions\stubTranslationFunctions();

        Functions\expect('get_transient')
            ->never();
    }

    public function testGetThrowsExceptionOnZeroLengthKey()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->cache->get('');
    }

    public function testGetThrowsExceptionOnOverMaxLengthKey()
    {
        $this->expectException(InvalidArgumentException::class);

        $key = str_repeat('k', TransientCache::KEY_MAX_LENGTH + 1);

        $this->cache->get($key);
    }

    public function testGetReturnsValueForExistingKey()
    {
        $key = 'key_exists';
        $valueForKey = 33;

        Functions\expect('get_transient')
            ->once()
            ->with($key)
            ->andReturn($valueForKey);

        $cachedValue = $this->cache->get($key);

        $this->assertSame($valueForKey, $cachedValue);
    }

    public function testGetReturnsNullForNotExistingKey()
    {
        $key = 'key_does_not_exist';
        $valueForKey = false;

        Functions\expect('get_transient')
            ->once()
            ->with($key)
            ->andReturn($valueForKey);

        $cachedValue = $this->cache->get($key);

        $this->assertNull($cachedValue);
    }

    public function testGetReturnsDefaultForNotExistingKey()
    {
        $key = 'key_does_not_exist';
        $valueForKey = false;

        Functions\expect('get_transient')
            ->once()
            ->with($key)
            ->andReturn($valueForKey);

        $defaultValue = 66;
        $cachedValue = $this->cache->get($key, $defaultValue);

        $this->assertSame($defaultValue, $cachedValue);
    }
}
