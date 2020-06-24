<?php
declare (strict_types = 1);

namespace GmossoEndpoint\Tests\DataProvider;

use GmossoEndpoint\Configuration;
use GmossoEndpoint\DataProvider\RestApiDataProvider;

use Brain\Monkey\Functions;
use Mockery;
use Seld\JsonLint\JsonParser;

trait RestApiCommonSetupTrait
{
    protected JsonParser $jsonParser;
    protected RestApiDataProvider $dataProvider;
    protected Configuration $configuration;
    protected string $endpoint;
    protected string $url;
    protected array $simpleResponse;

    protected function commonSetup(): void
    {
        parent::setUp();

        // create instance of data provider
        $logger = Mockery::mock('\Psr\Log\AbstractLogger');
        $logger->shouldReceive('info', 'error');

        $this->jsonParser = Mockery::namedMock(
            'Seld\JsonLint\JsonParser',
            'GmossoEndpoint\Tests\DataProvider\JsonParserStub'
        );

        $this->configuration = Mockery::mock(
            'GmossoEndpoint\Configuration'
        );
        $this->configuration->shouldReceive('offsetGet')
            ->with('API_ROOT')
            ->andReturn('http://example.com');

        $this->dataProvider = new RestApiDataProvider(
            $logger,
            $this->jsonParser,
            $this->configuration
        );

        // Default mocks
        $this->endpoint = 'my-endpoint';
        $this->url = $this->configuration['API_ROOT'] . "/{$this->endpoint}";

        Functions\stubTranslationFunctions();

        $this->simpleResponse = $this->simpleResponse();
    }

    protected function simpleResponse(): array
    {
        return [
            'headers' => ['server' => 'nginx', 'cache-control' => 'no-store'],
            'body' => '[ { "id": 1, "name": "Leanne Graham", "username": "Bret"},' .
                ' { "id": 2, "name": "Ervin Howell", "username": "Antonette"} ]',
        ];
    }
}
