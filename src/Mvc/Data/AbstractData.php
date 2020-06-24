<?php
declare(strict_types=1);

/**
 * Abstract representation of data
 *
 * Represents the data that must be displayed, read from the DataProvider and
 * injected into the view to output them.
 *
 * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\Mvc\Data;

abstract class AbstractData
{
    protected array $data;

    /**
     * Class constructor.
     *
     * @since 1.0.0
     * @param array $data data to store for later use
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Returns the stored data.
     *
     * @since  1.0.0
     * @return array data stored
     */
    public function data(): array
    {
        return $this->data;
    }
}
