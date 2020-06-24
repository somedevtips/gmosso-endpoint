<?php
declare(strict_types=1);

/**
 * Abstract data provider implementation
 *
 * Abstract implementation of a class that reads the data from a source.
 *
 * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\DataProvider;

use GmossoEndpoint\Configuration;

use Psr\Log\AbstractLogger;
use Seld\JsonLint\JsonParser;

abstract class AbstractDataProvider
{
    protected AbstractLogger $logger;
    protected JsonParser $jsonParser;
    protected Configuration $configuration;
    protected bool $cacheable;
    protected int $cacheTtl;

    /**
     * Class constructor.
     *
     * @since 1.0.0
     * @param AbstractLogger $logger        logger instance
     * @param JsonParser     $jsonParser    JsonParser instance
     * @param Configuration  $configuration Configuration instance
     */
    public function __construct(
        AbstractLogger $logger,
        JsonParser $jsonParser,
        Configuration $configuration
    ) {

        $this->logger = $logger;
        $this->jsonParser = $jsonParser;
        $this->configuration = $configuration;
        $this->cacheable = false;
        $this->cacheTtl = 0;
    }

    /**
     * Reads and returns the data from the source.
     *
     * @since  1.0.0
     * @param  string $endpoint endpoint of the source to read from
     * @return array            data returned from the source, validated and parsed.
     *                          Every array element contains all data of an item
     */
    abstract public function readData(string $endpoint): array;

    /**
     * Returns if data read can be saved in a cache.
     *
     * @since  1.0.0
     * @return bool true if the data read from the source can be saved in a cache
     */
    public function dataAreCacheable(): bool
    {
        return $this->cacheable;
    }

    /**
     * Returns the time to live to set when data are saved in tha cache.
     *
     * @since  1.0.0
     * @return int ttl value in seconds, 0 if data cannot be cached
     */
    public function cacheTtl(): int
    {
        return $this->cacheTtl;
    }
}
