<?php
declare (strict_types = 1);

namespace GmossoEndpoint\Tests\DataProvider;

use GmossoEndpoint\Tests\GmossoEndpointTestCase;

use Brain\Monkey\Functions;

class RestApiDataProviderCacheInfoTest extends GmossoEndpointTestCase
{
    use RestApiCommonSetupTrait;
    use RestApiReadDataOKExpectationsTrait;

    protected function setUp(): void
    {
        $this->commonSetup(); //in RestApiCommonSetupTrait
    }

    public function testNotCacheableIfCacheControlMissing()
    {
        $response = $this->simpleResponse();
        unset($response['headers']['cache-control']);

        $this->expectationsForSuccessfulReadData($response);

        $this->dataProvider->readData($this->endpoint);

        $dataAreCacheable = $this->dataProvider->dataAreCacheable();

        $this->assertSame(false, $dataAreCacheable);
    }

    public function testNotCacheableIfCacheControlEmpty()
    {
        $response = $this->simpleResponse();
        $response['headers']['cache-control'] = '';

        $this->expectationsForSuccessfulReadData($response);

        $this->dataProvider->readData($this->endpoint);

        $dataAreCacheable = $this->dataProvider->dataAreCacheable();

        $this->assertSame(false, $dataAreCacheable);
    }

    /**
     * @dataProvider cacheControlDirectiveProvider
     */
    public function testNotCacheableIfCacheControlPreventsIt(
        string $directive,
        bool $cacheable
    ) {

        $response = $this->simpleResponse();
        $response['headers']['cache-control'] = $directive;

        $this->expectationsForSuccessfulReadData($response);

        $this->dataProvider->readData($this->endpoint);

        $dataAreCacheable = $this->dataProvider->dataAreCacheable();

        $this->assertSame($cacheable, $dataAreCacheable);
    }

    public function testCacheableIfMaxAgeGreaterThanZero()
    {
        $response = $this->simpleResponse();
        $response['headers']['cache-control'] = 'public,max-age=300';

        $this->expectationsForSuccessfulReadData($response);

        $this->dataProvider->readData($this->endpoint);

        $dataAreCacheable = $this->dataProvider->dataAreCacheable();

        $this->assertSame(true, $dataAreCacheable);
    }

    public function testCacheableAndTtlSetToMaxAgeValueIfMaxAgeGreaterThanZero()
    {
        $ttl = 300;
        $response = $this->simpleResponse();
        $response['headers']['cache-control'] = "max-age={$ttl}";

        $this->expectationsForSuccessfulReadData($response);

        $this->dataProvider->readData($this->endpoint);

        $dataAreCacheable = $this->dataProvider->dataAreCacheable();
        $cacheTtl = $this->dataProvider->cacheTtl();

        $this->assertSame(true, $dataAreCacheable);
        $this->assertSame($ttl, $cacheTtl);
    }

    public function cacheControlDirectiveProvider(): array
    {
        return [
            ['private', false],
            ['no-cache', false],
            ['no-store', false],
            ['public', false],
            ['public,max-age=0', false],
        ];
    }
}
