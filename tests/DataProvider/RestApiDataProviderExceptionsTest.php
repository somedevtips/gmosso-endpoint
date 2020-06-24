<?php
declare (strict_types = 1);

namespace GmossoEndpoint\Tests\DataProvider;

use GmossoEndpoint\Tests\GmossoEndpointTestCase;

use GmossoEndpoint\DataProvider\RestApiException;

use Brain\Monkey\Functions;
use Mockery;

class RestApiDataProviderExceptionsTest extends GmossoEndpointTestCase
{
    use RestApiCommonSetupTrait;

    //other variables
    protected object $wpError;

    protected function setUp(): void
    {
        $this->commonSetup(); //in RestApiCommonSetupTrait

        $this->wpError = Mockery::mock('WP_Error');
        $this->wpError->shouldReceive('get_error_messages')
            ->andReturn(['error string of WP_Error']);
    }

    public function testThrowsRestApiExceptionOnWpError()
    {
        Functions\expect('wp_remote_get')
            ->once()
            ->with($this->url)
            ->andReturn($this->wpError);

        Functions\expect('is_wp_error')
            ->once()
            ->with($this->wpError)
            ->andReturn(true);

        $this->expectException(RestApiException::class);

        $this->dataProvider->readData($this->endpoint);
    }

    public function testThrowsRestApiExceptionOnNot200HttpCode()
    {
        Functions\expect('wp_remote_get')
            ->once()
            ->with($this->url)
            ->andReturn($this->simpleResponse);

        Functions\expect('is_wp_error')
            ->once()
            ->with($this->simpleResponse)
            ->andReturn(false);

        Functions\expect('wp_remote_retrieve_response_code')
            ->once()
            ->with($this->simpleResponse)
            ->andReturn(404);

        $this->expectException(RestApiException::class);

        $this->dataProvider->readData($this->endpoint);
    }

    public function testThrowsRestApiExceptionOnParsingException()
    {
        Functions\expect('wp_remote_get')
            ->once()
            ->with($this->url)
            ->andReturn($this->simpleResponse);

        Functions\expect('is_wp_error')
            ->once()
            ->with($this->simpleResponse)
            ->andReturn(false);

        Functions\expect('wp_remote_retrieve_response_code')
            ->once()
            ->with($this->simpleResponse)
            ->andReturn(200);

        Functions\expect('wp_remote_retrieve_body')
            ->once()
            ->with($this->simpleResponse)
            ->andReturn($this->simpleResponse['body']);

        $this->jsonParser->shouldReceive('parse')
            ->once()
            ->with($this->simpleResponse['body'], JsonParserStub::PARSE_TO_ASSOC)
            ->andThrow(new \Seld\JsonLint\ParsingException('parsing error'));

        $this->expectException(RestApiException::class);

        $this->dataProvider->readData($this->endpoint);
    }
}
