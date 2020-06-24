<?php
declare (strict_types = 1);

/**
 * Bootstrap interface
 *
 * Interface implemented by all classes involved in plugin boostrap.
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint;

interface BootstrappableInterface
{
    public function bootstrap(): void;
}
