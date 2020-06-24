<?php
declare (strict_types = 1);

namespace GmossoEndpoint\Tests\Users;

use GmossoEndpoint\Tests\GmossoEndpointTestCase;

use GmossoEndpoint\Configuration;
use GmossoEndpoint\DataProvider\AbstractDataProvider;
use GmossoEndpoint\DataProvider\DataProviderException;
use GmossoEndpoint\Mvc\Data\ErrorData;
use GmossoEndpoint\SimpleCache\CacheInterface;
use GmossoEndpoint\Users\AllUsersData;
use GmossoEndpoint\Users\Model as UserModel;

use Mockery;
use Brain\Monkey\Functions;

class ModelAllItemsTest extends GmossoEndpointTestCase
{
    protected UserModel $model;
    protected AbstractDataProvider $dataProvider;
    protected CacheInterface $cache;
    protected Configuration $configuration;
    protected string $modelKey;
    protected string $transientKey;
    protected TestData $testData;

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

        $this->dataProvider =
            Mockery::mock('GmossoEndpoint\DataProvider\AbstractDataProvider');

        $this->cache =
            Mockery::mock('\GmossoEndpoint\SimpleCache\CacheInterface');
        $this->cache->shouldReceive('get')
            ->with($this->transientKey, false)
            ->andReturn(false)
            ->byDefault();

        $this->model = new UserModel(
            $this->dataProvider,
            $this->cache,
            $this->configuration
        );
    }

    public function testErrorDataReturnedIfDataProviderThrowsException()
    {
        $this->dataProvider->shouldReceive('readData')
            ->with($this->modelKey)
            ->andThrow(new DataProviderException());

        $data = $this->model->allItems();

        $this->assertInstanceOf(ErrorData::class, $data);
    }

    public function testAllUsersDataReturnedIfDataProviderReadDataOK()
    {
        $itemsJson = $this->testData->allUsersData();

        $this->dataProvider->shouldReceive('readData')
            ->with($this->modelKey)
            ->andReturn($itemsJson->data());

        $this->dataProvider->shouldReceive('dataAreCacheable')
            ->withNoArgs()
            ->andReturn(false);

        $data = $this->model->allItems();

        $this->assertInstanceOf(AllUsersData::class, $data);
        $this->assertSame($itemsJson->data(), $data->data());
    }

    public function testdataCachedIfDataProviderReadDataOKAndDataAreCacheable()
    {
        $itemsJson = $this->testData->allUsersData();
        $ttl = 10000;

        $this->dataProvider->shouldReceive('readData')
            ->with($this->modelKey)
            ->andReturn($itemsJson->data());

        $this->dataProvider->shouldReceive('dataAreCacheable')
            ->withNoArgs()
            ->andReturn(true);

        $this->dataProvider->shouldReceive('cacheTtl')
            ->withNoArgs()
            ->andReturn($ttl);

        $this->cache->shouldReceive('set')
            ->with($this->transientKey, $itemsJson->data(), $ttl);

        $data = $this->model->allItems();

        $this->assertInstanceOf(AllUsersData::class, $data);
        $this->assertSame($itemsJson->data(), $data->data());
    }

    public function testAllUsersDataReturnedIfDataAreInCache()
    {
        $users = $this->testData->allUsersData()->data();

        $this->cache->shouldReceive('get')
            ->with($this->transientKey, false)
            ->andReturn($users);

        $data = $this->model->allItems();

        $this->assertInstanceOf(AllUsersData::class, $data);
        $this->assertSame($users, $data->data());
    }
}
