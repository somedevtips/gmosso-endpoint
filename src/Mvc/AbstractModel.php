<?php
declare(strict_types=1);

/**
 * Base class for models
 *
 * Abstract representation of a Mvc model.
 *
 * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\Mvc;

use GmossoEndpoint\DataProvider\AbstractDataProvider;
use GmossoEndpoint\DataProvider\DataProviderException;
use GmossoEndpoint\Configuration;
use GmossoEndpoint\Mvc\Data\AbstractData;
use GmossoEndpoint\Mvc\Data\ErrorData;
use GmossoEndpoint\SimpleCache\CacheInterface;

abstract class AbstractModel
{
    protected AbstractDataProvider $dataProvider;
    protected CacheInterface $cache;
    protected Configuration $configuration;
    protected string $modelKey = 'defined_in_concrete_class';
    protected string $transientKey;

    /**
     * Class constructor.
     *
     * @since 1.0.0
     * @param AbstractDataProvider $dataProvider  instance of the DataProvider
     * @param CacheInterface       $cache         instance of the cache
     * @param Configuration        $configuration Configuration instance
     */
    public function __construct(
        AbstractDataProvider $dataProvider,
        CacheInterface $cache,
        Configuration $configuration
    ) {

        $this->dataProvider =  $dataProvider;
        $this->cache = $cache;
        $this->configuration = $configuration;
        $this->transientKey =
            $this->configuration['PLUGIN_PREFIX'] . '_' . $this->modelKey;
    }

    /**
     * Returns the data of all the items
     *
     * @since  1.0.0
     * @return AbstractData the data of all the items
     */
    public function allItems(): AbstractData
    {
        $itemsJson = $this->cache->get($this->transientKey, false);

        if ($itemsJson === false) {
            try {
                $itemsJson = $this->dataProvider->readData($this->modelKey);
            } catch (DataProviderException $exc) {
                return $this->errorData($exc->getMessage());
            }

            // Cache data if possible
            if ($this->dataProvider->dataAreCacheable()) {
                $this->cache->set(
                    $this->transientKey,
                    $itemsJson,
                    $this->dataProvider->cacheTtl()
                );
            }
        }

        return $this->allItemsData($itemsJson);
    }

    /**
     * Returns the data of a single item.
     *
     * @since  1.0.0
     * @param  int    $itemId id of the item
     * @return AbstractData   item data
     */
    public function singleItem(int $itemId): AbstractData
    {
        $allItemsData = $this->allItems();
        if ($allItemsData instanceof ErrorData) {
            return $allItemsData;
        }

        $allItems = $allItemsData->data();

        $filteredItems = array_filter(
            $allItems,
            function (array $item) use ($itemId): bool {
                return ($item['id'] === $itemId);
            }
        );

        if (count($filteredItems) === 0) {
            return $this->errorData(
                sprintf(
                    /* translators: %d: identifier of the item that was not found */
                    __('item %d not found', 'gmosso-endpoint'),
                    $itemId
                )
            );
        }

        return $this->singleItemData(array_pop($filteredItems));
    }

    /**
     * Returns the key of the transient where the items are cached (if possible)
     *
     * @since  1.0.0
     * @return string transient key
     */
    public function transientKey(): string
    {
        return $this->transientKey;
    }

    /**
     * ErrorData factory.
     *
     * Returns a new instance of ErrorData
     *
     * @since  1.0.0
     * @param  string $errorMessage error description
     * @return AbstractData         ErrorData for the error message
     */
    public function errorData(string $errorMessage): AbstractData
    {
        return new ErrorData([$errorMessage]);
    }

    /**
     * Factory for data of all items
     *
     * @since  1.0.0
     * @param  array  $items array containing all items
     * @return AbstractData  data of all items
     */
    abstract protected function allItemsData(array $items): AbstractData;

    /**
     * Factory for data of a single item
     *
     * @since  1.0.0
     * @param  array  $data array containing the single item
     * @return AbstractData data of the single item
     */
    abstract protected function singleItemData(array $data): AbstractData;
}
