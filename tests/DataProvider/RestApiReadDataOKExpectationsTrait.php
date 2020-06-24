<?php
declare (strict_types = 1);

namespace GmossoEndpoint\Tests\DataProvider;

use Brain\Monkey\Functions;

trait RestApiReadDataOKExpectationsTrait
{
    protected function expectationsForSuccessfulReadData(array $response): void
    {
        Functions\expect('wp_remote_get')
            ->once()
            ->with($this->url)
            ->andReturn($response);

        Functions\expect('is_wp_error')
            ->once()
            ->with($response)
            ->andReturn(false);

        Functions\expect('wp_remote_retrieve_response_code')
            ->once()
            ->with($response)
            ->andReturn(200);

        Functions\expect('wp_remote_retrieve_body')
            ->once()
            ->with($response)
            ->andReturn($response['body']);

        $this->jsonParser->shouldReceive('parse')
            ->once()
            ->with($response['body'], JsonParserStub::PARSE_TO_ASSOC)
            ->andReturn(json_decode($response['body'], true));

        Functions\expect('wp_remote_retrieve_header')
            ->once()
            ->with($response, 'cache-control')
            ->andReturn($response['headers']['cache-control'] ?? '');
    }
}
