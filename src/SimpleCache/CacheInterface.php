<?php
declare(strict_types=1);

/**
 * Cache interface
 *
 * A PSR16-like interface for caching.
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\SimpleCache;

interface CacheInterface
{
    /**
     * Fetches a value from the cache.
     *
     * @since 1.0.0
     * @param string $key     The unique key of this item in the cache.
     * @param mixed  $default Default value to return if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     *
     * Disable the following rules because the $default type can be any serializable type
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     */
    public function get(string $key, $default = null);
    // phpcs:enable

    /**
     * Persists data in the cache, data are referenced by a key with an optional expiration TTL time.
     *
     * @since 1.0.0
     * @param string                 $key   The key of the item to store.
     * @param mixed                  $value The value of the item to store, must be serializable.
     * @param null|int|\DateInterval $ttl   Optional. The TTL value of this item. If no value is sent
     *                                      and the driver supports TTL, then the library may set a
     *                                      default value for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     *
     * Disable the following rules because the $value type can be any serializable type
     * and the $ttl type can be null|int|\DateInterval
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     */
    public function set(string $key, $value, $ttl = null): bool;
    // phpcs:enable

    /**
     * Delete an item from the cache by its unique key.
     *
     * @since 1.0.0
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function delete(string $key): bool;
}
