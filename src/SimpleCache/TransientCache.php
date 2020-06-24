<?php
declare(strict_types=1);

/**
 * CacheInterface implementation
 *
 * Contains the implementation of a cache that uses WordPress transients.
 *
 * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\SimpleCache;

class TransientCache implements CacheInterface
{
    public const KEY_MAX_LENGTH = 172;

    /**
     * @inheritDoc
     *
     * Disable the following rules because this is an implementation of the
     * interface CacheInterface
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     * phpcs:disable Inpsyde.CodeQuality.ReturnTypeDeclaration.NoReturnType
     */
    public function get(string $key, $default = null)
    {
        // phpcs:enable
        $key = $this->validateAndReturnKey($key);

        $value = get_transient($key);
        if ($value === false) {
            return $default;
        }

        return $value;
    }

    /**
     * @inheritDoc
     *
     * $ttl null here means no expiration
     *
     * Disable the following rules because this is an implementation of the
     * interface CacheInterface
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     */
    public function set(string $key, $value, $ttl = null): bool
    {
        // phpcs:enable
        $key = $this->validateAndReturnKey($key);

        $expiration = $this->validateAndReturnTtl($ttl);

        if (is_null($expiration)) {
            return set_transient($key, $value, 0);
        }

        // psr 16 requirement
        if ($expiration <= 0) {
            return $this->delete($key);
        }

        return set_transient($key, $value, $expiration);
    }

    /**
     * @inheritDoc
     *
     */
    public function delete(string $key): bool
    {
        $key = $this->validateAndReturnKey($key);
        return delete_transient($key);
    }

    /**
     * Validates and returns the key.
     *
     * @since 1.0.0
     * @param string $key
     * @return string
     * @throws InvalidArgumentException if the key is not valid
     */
    protected function validateAndReturnKey(string $key): string
    {
        if (strlen($key) > 0 and
            strlen($key) <= self::KEY_MAX_LENGTH
        ) {
            return $key;
        }

        throw new InvalidArgumentException(
            sprintf(
                /* translators: %d: number that indicates the max length */
                __(
                    'Key must be not empty string with max length %d',
                    'gmosso-endpoint'
                ),
                self::KEY_MAX_LENGTH
            )
        );
    }

    /**
     * Validates and returns the ttl.
     *
     * Edge cases:
     * $ttl === null => no expiration
     * $ttl <= 0 => delete the item (psr16 requirement)
     *
     * @since 1.0.0
     * @param null|int|dateInterval $ttl
     * @return int value of the ttl
     * @throws InvalidArgumentException if the ttl value is not valid
     *
     * Disable the following rules because this receives the untyped $ttl
     * argument of the caller
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     */
    protected function validateAndReturnTtl($ttl): ?int
    {
        // phpcs:enable
        if (is_null($ttl)) {
            return null;
        }

        if (is_numeric($ttl)) {
            return (int)$ttl;
        }

        if ($ttl instanceof \DateInterval) {
            return (new \DateTime())
                ->setTimestamp(0)
                ->add($ttl)
                ->getTimestamp();
        }

        throw new InvalidArgumentException(
            /* translators: do not translate null|int|dateInterval */
            __('ttl value must be null|int|dateInterval', 'gmosso-endpoint')
        );
    }
}
