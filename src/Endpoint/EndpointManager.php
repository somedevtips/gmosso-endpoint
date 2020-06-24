<?php
declare (strict_types = 1);

/**
 * Management of WordPress endpoints
 *
 * Manages adding the endpoints to WordPress, except flushing the rewrite rules
 * that is done by the Installer module to optimize perfomance.
 *
 * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\Endpoint;

use GmossoEndpoint\BootstrappableInterface;
use GmossoEndpoint\Configuration;

class EndpointManager implements BootstrappableInterface
{
    private Configuration $configuration;

    /**
     * Class constructor.
     *
     * @since 1.0.0
     * @param Configuration $configuration Configuration instance
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Sets callbacks for hooks at plugin bootstrap.
     *
     * @since  1.0.0
     * @return void
     */
    public function bootstrap(): void
    {
        add_action('init', [$this, 'addEndpoints']);
    }

    /**
     * Callback function that adds the endpoints
     *
     * @since 1.0.0
     * @global int EP_ROOT Endpoint Mask for root
     * @return void
     */
    public function addEndpoints(): void
    {
        $endpointPrefix = $this->configuration['ENDPOINT_PREFIX'];
        $endpoints = $this->configuration['ENDPOINTS'];

        foreach ($endpoints as $endpoint => $endpointSettings) {
            add_rewrite_endpoint(
                "{$endpointPrefix}{$endpoint}",
                EP_ROOT
            );
        }
    }
}
