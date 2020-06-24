<?php
declare(strict_types=1);

/**
 * Container of controllers
 *
 * Collection of AbstractController instances.
 *
 * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\Mvc;

class Controllers implements \ArrayAccess
{
    protected array $list = [];

    /**
     * Adds a new AbstractController to the collection.
     *
     * @since  1.0.0
     * @param  mixed $offset controller key, must be a string
     * @param  mixed $value  controller instance
     * @return void
     * @throws \InvalidArgumentException if $offset is not a string or
     *                                   $value is not an AbstractController
     */
    public function offsetSet($offset, $value): void
    {
        if (!is_string($offset)) {
            throw new \InvalidArgumentException(
                __('key must be a string', 'gmosso-endpoint')
            );
        }

        if (!$value instanceof AbstractController) {
            throw new \InvalidArgumentException(
                __('value must be AbstractController type', 'gmosso-endpoint')
            );
        }

        $this->list[$offset] = $value;
    }

    /**
     * Returns a controller given its key
     *
     * @since  1.0.0
     * @param  mixed $offset controller key, must be a string
     * @return AbstractController controller instance
     * @throws \OutOfRangeException if key is not present in collection
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfRangeException(
                sprintf(
                    /* translators: %s if a string that identifies the controller */
                    __(
                        'controller with key %s not found in collection',
                        'gmosso-endpoint'
                    ),
                    $offset
                )
            );
        }
        return $this->list[$offset];
    }

    /**
     * Removes a controller from the collection
     *
     * @since  1.0.0
     * @param  mixed $offset controller key, must be a string
     * @return void
     * @throws \OutOfRangeException if key is not present in collection
     */
    public function offsetUnset($offset): void
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfRangeException(
                sprintf(
                    /* translators: %s if a string that identifies the controller */
                    __(
                        'controller with key %s not found in collection',
                        'gmosso-endpoint'
                    ),
                    $offset
                )
            );
        }
        unset($this->list[$offset]);
    }

    /**
     * Checks if a controller exists in collection
     *
     * @since  1.0.0
     * @param  mixed $offset controller key, must be a string
     * @return bool          true if controller exists
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->list);
    }

    /**
     * Returns the first controller of the collection
     *
     * @since  1.0.0
     * @return AbstractController|null first controller or null if collection
     *                                 is empty
     */
    public function firstController(): ?AbstractController
    {
        if (count($this->list) === 0) {
            return null;
        }

        return array_values($this->list)[0];
    }
}
