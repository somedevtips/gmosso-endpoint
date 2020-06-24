<?php
declare(strict_types=1);

/**
 * Configuration class
 *
 * Manages the configuration of this plugin.
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint;

class Configuration implements \ArrayAccess
{
    /**
     * All parameters of this plugin.
     *
     * @var array
     */
    protected array $parameters = [
        'API_ROOT' => 'https://jsonplaceholder.typicode.com',
        'DEBUG' => 0,
        'ENDPOINT_PREFIX' => 'gmosso-',
        'ENDPOINTS' => [
            'users' => [
                'template' => 'users.php',
            ],
        ],
        'MIN_PHP_VERSION' => '7.4.0',
        'OPTION_INSTALLED' => 'installed',
        'OPTION_NAME' => 'gmosso_endpoint_options',
        'OPTION_TRANSIENTS' => 'transients',
        'PLUGIN_PREFIX' => 'gmosso_endpoint',
        'TEMPLATE_DIR' => 'templates',
    ];

    /**
     * Returns a parameter of the configuration.
     *
     * @since  1.0.0
     * @param  mixed $offset key of the parameter
     * @return mixed         value of the parameter
     * @throws \OutOfRangeException if the parameter requested does not exist
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfRangeException(
                sprintf(
                    /* translators: %s name of a parameter of the plugin */
                    __(
                        '%s parameter does not exist',
                        'gmosso-endpoint'
                    ),
                    $offset
                )
            );
        }

        return $this->parameters[$offset];
    }

    /**
     * Checks if a parameter exists.
     *
     * @since  1.0.0
     * @param  mixed $offset key of the parameter
     * @return bool         true if the parameter does not exist.
     */
    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->parameters);
    }

    /**
     * @since  1.0.0
     * @throws \BadMethodCallException because it is not allowed
     */
    public function offsetSet($offset, $value): void
    {
        throw new \BadMethodCallException(
            sprintf(
                /* translators: 1: name of PHP method 2: name of PHP class */
                __(
                    'calling %1$s not allowed because class %2$s is read only',
                    'gmosso-endpoint'
                ),
                __METHOD__,
                __CLASS__
            )
        );
    }

    /**
     * @since  1.0.0
     * @throws \BadMethodCallException because it is not allowed
     */
    public function offsetUnset($offset): void
    {
        throw new \BadMethodCallException(
            sprintf(
                /* translators: 1: name of PHP method 2: name of PHP class */
                __(
                    'calling %1$s not allowed because class %2$s is read only',
                    'gmosso-endpoint'
                ),
                __METHOD__,
                __CLASS__
            )
        );
    }
}
