<?php
declare(strict_types=1);

/**
 * Abstract representation of a view
 *
 * Represents a view that receives AbstractData and defines how they are
 * displayed to the user.
 *
 * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\Mvc\View;

use GmossoEndpoint\Mvc\Data\AbstractData;

abstract class AbstractView
{
    protected AbstractData $viewData;

    /**
     * Class constructor.
     *
     * @since 1.0.0
     * @param AbstractData $viewData data that must be displayed to the user
     */
    public function __construct(AbstractData $viewData)
    {
        $this->viewData = $viewData;
    }

    /**
     * Creates the array to output in json format
     *
     * @since  1.0.0
     * @return array array to encode in json format
     */
    protected function prepareJsonOutput(): array
    {
        return ['html' => $this->html()];
    }

    /**
     * Returns the html representation of the data.
     *
     * @since  1.0.0
     * @return string html markup to display for the data
     */
    abstract public function html(): string;

    /**
     * Outputs the json representation of the data.
     *
     * @since  1.0.0
     * @return void
     */
    abstract public function renderJson(): void;
}
