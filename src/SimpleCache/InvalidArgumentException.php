<?php
declare(strict_types=1);

/**
 * InvalidArgumentException class
 *
 * Defines a InvalidArgumentException for this module.
 *
 * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\SimpleCache;

class InvalidArgumentException extends \InvalidArgumentException implements
    CacheException
{
}
