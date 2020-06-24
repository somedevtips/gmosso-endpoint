<?php
declare (strict_types = 1);

namespace GmossoEndpoint\Tests\SimpleCache;

use GmossoEndpoint\SimpleCache\InvalidArgumentException;
use GmossoEndpoint\SimpleCache\TransientCache;
use GmossoEndpoint\Tests\GmossoEndpointTestCase;

use Brain\Monkey\Functions;

class TransientCacheSetTest extends GmossoEndpointTestCase
{
    protected TransientCache $cache;
    protected string $dummyKey;
    protected array $dummyValue;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = new TransientCache();
        $this->dummyKey = 'k';
        $this->dummyValue = ['key1' => 'val1', 3 => 5.6];

        Functions\stubTranslationFunctions();

        Functions\expect('set_transient')
            ->never();

        Functions\expect('delete_transient')
            ->never();
    }

    public function testSetThrowsExceptionOnZeroLengthKey()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->cache->set('', $this->dummyValue);
    }

    public function testSetThrowsExceptionOnOverMaxLengthKey()
    {
        $this->expectException(InvalidArgumentException::class);

        $key = str_repeat('k', TransientCache::KEY_MAX_LENGTH + 1);

        $this->cache->set($key, $this->dummyValue);
    }

    public function testSetZeroExpirationForNullTtl()
    {
        Functions\expect('set_transient')
            ->once()
            ->with($this->dummyKey, $this->dummyValue, 0)
            ->andReturn(true);

        $result = $this->cache->set($this->dummyKey, $this->dummyValue);

        $this->assertSame(true, $result);
    }

    public function testSetExpirationForIntegerTtl()
    {
        $ttl = 14400;

        Functions\expect('set_transient')
            ->once()
            ->with($this->dummyKey, $this->dummyValue, $ttl)
            ->andReturn(true);

        $result = $this->cache->set($this->dummyKey, $this->dummyValue, $ttl);

        $this->assertSame(true, $result);
    }

    public function testSetExpirationForNumericStringTtl()
    {
        $ttl = '14400';

        Functions\expect('set_transient')
            ->once()
            ->with($this->dummyKey, $this->dummyValue, (int)$ttl)
            ->andReturn(true);

        $result = $this->cache->set($this->dummyKey, $this->dummyValue, $ttl);

        $this->assertSame(true, $result);
    }

    public function testSetExpirationForDateIntervalTtl()
    {
        $ttl = new \DateInterval('PT300S');

        Functions\expect('set_transient')
            ->once()
            ->with($this->dummyKey, $this->dummyValue, 300)
            ->andReturn(true);

        $result = $this->cache->set($this->dummyKey, $this->dummyValue, $ttl);

        $this->assertSame(true, $result);
    }

    public function testTtlInvalidTypeThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->cache->set($this->dummyKey, $this->dummyValue, 'aa');
    }

    public function testNegativeTtlRemovesFromCache()
    {
        Functions\expect('delete_transient')
            ->once()
            ->with($this->dummyKey)
            ->andReturn(true);

        $result = $this->cache->set($this->dummyKey, $this->dummyValue, -1);

        $this->assertSame(true, $result);
    }
}
