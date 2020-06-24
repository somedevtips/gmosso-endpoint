<?php
declare (strict_types = 1);

namespace GmossoEndpoint\Tests\SimpleCache;

use GmossoEndpoint\SimpleCache\InvalidArgumentException;
use GmossoEndpoint\SimpleCache\TransientCache;
use GmossoEndpoint\Tests\GmossoEndpointTestCase;

use Brain\Monkey\Functions;

class TransientCacheDeleteTest extends GmossoEndpointTestCase
{
    protected TransientCache $cache;
    protected string $dummyKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cache = new TransientCache();
        $this->dummyKey = 'k';

        Functions\stubTranslationFunctions();

        Functions\expect('delete_transient')
            ->never();
    }

    public function testDeleteThrowsExceptionOnZeroLengthKey()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->cache->delete('');
    }

    public function testDeleteThrowsExceptionOnOverMaxLengthKey()
    {
        $this->expectException(InvalidArgumentException::class);

        $key = str_repeat('k', TransientCache::KEY_MAX_LENGTH + 1);

        $this->cache->delete($key);
    }

    public function testDelete()
    {
        Functions\expect('delete_transient')
            ->once()
            ->with($this->dummyKey)
            ->andReturn(true);

        $result = $this->cache->delete($this->dummyKey);

        $this->assertSame(true, $result);
    }
}
