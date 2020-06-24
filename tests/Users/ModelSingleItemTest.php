<?php
declare (strict_types = 1);

namespace GmossoEndpoint\Tests\Users;

use GmossoEndpoint\Tests\GmossoEndpointTestCase;

use GmossoEndpoint\Configuration;
use GmossoEndpoint\DataProvider\AbstractDataProvider;
use GmossoEndpoint\DataProvider\DataProviderException;
use GmossoEndpoint\Mvc\Data\ErrorData;
use GmossoEndpoint\SimpleCache\CacheInterface;
use GmossoEndpoint\Users\SingleUserData;
use GmossoEndpoint\Users\Model as UserModel;

use Mockery;
use Brain\Monkey\Functions;

class ModelSingleItemTest extends GmossoEndpointTestCase
{
    protected UserModel $model;
    protected AbstractDataProvider $dataProvider;
    protected CacheInterface $cache;
    protected Configuration $configuration;
    protected string $modelKey;
    protected string $transientKey;
    protected TestData $testData;
    protected array $users;

    public const MOCKED_PLUGIN_PREFIX_VALUE = 'mocked_plugin_prefix';

    protected function setUp(): void
    {
        parent::setUp();

        $this->modelKey = 'users';

        $this->configuration = Mockery::mock(
            'GmossoEndpoint\Configuration'
        );
        $this->configuration->shouldReceive('offsetGet')
            ->with('PLUGIN_PREFIX')
            ->andReturn(self::MOCKED_PLUGIN_PREFIX_VALUE);

        $this->transientKey =
            self::MOCKED_PLUGIN_PREFIX_VALUE . '_' . $this->modelKey;

        $this->testData = new TestData();

        $this->users = $this->testData->allUsersData()->data();

        $this->dataProvider =
            Mockery::mock('GmossoEndpoint\DataProvider\AbstractDataProvider');

        $this->cache =
            Mockery::mock('\GmossoEndpoint\SimpleCache\CacheInterface');
        $this->cache->shouldReceive('get')
            ->with($this->transientKey, false)
            ->andReturn($this->users)
            ->byDefault();

        $this->model = new UserModel(
            $this->dataProvider,
            $this->cache,
            $this->configuration
        );

        Functions\stubTranslationFunctions();
    }

    public function testErrorDataReturnedIfDataProviderThrowsException()
    {
        $this->cache->shouldReceive('get')
            ->with($this->transientKey, false)
            ->andReturn(false);

        $this->dataProvider->shouldReceive('readData')
            ->with($this->modelKey)
            ->andThrow(new DataProviderException());

        $data = $this->model->singleItem(1);

        $this->assertInstanceOf(ErrorData::class, $data);
    }

    public function testErrorDataReturnedIfUserNotFound()
    {
        $maxId = $this->testData->maxIdAllUsers();

        $data = $this->model->singleItem($maxId + 1);

        $this->assertInstanceOf(ErrorData::class, $data);
    }

    public function testSingleUserDataReturnedIfUserFound()
    {
        $maxId = $this->testData->maxIdAllUsers();

        $data = $this->model->singleItem($maxId);

        $this->assertInstanceOf(SingleUserData::class, $data);
    }
}
