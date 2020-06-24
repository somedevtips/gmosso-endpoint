<?php
declare(strict_types=1);

/**
 * Routing management
 *
 * Interface between the WordPress routing logic and the Mvc layer.
 *
 * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\Mvc;

use GmossoEndpoint\Configuration;
use GmossoEndpoint\BootstrappableInterface;

class Router implements BootstrappableInterface
{
    private Controllers $controllers;
    private Configuration $configuration;

    /**
     * Class constructor.
     *
     * @since 1.0.0
     * @param Controllers   $controllers   collection of all controllers
     * @param Configuration $configuration Configuration instance
     */
    public function __construct(
        Controllers $controllers,
        Configuration $configuration
    ) {

        $this->controllers = $controllers;
        $this->configuration = $configuration;
    }

    /**
     * Callbacks for WordPress hooks fired at template definition or ajax calls
     *
     * @since  1.0.0
     * @return void
     */
    public function bootstrap(): void
    {
        $ajaxActionGetItem = $this->ajaxActionGetItem();

        add_filter('template_include', [$this, 'route']);
        add_action(
            'wp_ajax_' . $ajaxActionGetItem,
            [$this, 'routeAjax']
        );
        add_action(
            'wp_ajax_nopriv_' . $ajaxActionGetItem,
            [$this, 'routeAjax']
        );
    }

    /**
     * Returns the endpoint template for calls to the endpoint, does not change
     * the template for the other urls.
     *
     * @since  1.0.0
     * @param  string $originalTemplate default template provided by WordPress
     * @return string                   template file to use
     */
    public function route(string $originalTemplate): string
    {
        $endpointPrefix = $this->configuration['ENDPOINT_PREFIX'];
        $endpoints = $this->configuration['ENDPOINTS'];
        foreach ($endpoints as $endpointKey => $endpointSettings) {
            $endpoint = $endpointPrefix . $endpointKey;
            $queryVarEndpoint = get_query_var($endpoint, false);

            if ($queryVarEndpoint === false) {
                continue;
            }

            if ($queryVarEndpoint === '') {
                return $this->controllers[$endpointKey]->allItemsTemplate();
            }
        }
        return $originalTemplate;
    }

    /**
     * Managas ajax calls.
     *
     * Reads and validates input data, dispatches the call to the correct
     * controller.
     *
     * @since  1.0.0
     * @return void
     */
    public function routeAjax(): void
    {
        // Read and validate input data
        [$itemId, $endpointKey] = $this->inputData();

        if (!in_array(
            $endpointKey,
            array_keys($this->configuration['ENDPOINTS']),
            true
        )
        ) {
            // Requested a not existing controller. Since this error does not
            // depend on the controller used, we get the first controller
            // in the list to output it
            $firstController = $this->controllers->firstController();
            $firstController->outputError(
                /* translators: do not translate 'endpoint' */
                __(
                    'endpoint must be one of the configured endpoints',
                    'gmosso-endpoint'
                )
            );
            //The following return statement is here only for a semantic reason
            //(router does not know that the call to the controller ends with a die)
            //and for unit tests (controller mock cannot contain a die() call)
            return;
        }

        if ($itemId === false) {
            $this->controllers[$endpointKey]->outputError(
                /* translators: do not translate 'itemId' */
                __('itemId must be integer', 'gmosso-endpoint')
            );
            //Please see comment to previous return statement
            return;
        }

        $this->controllers[$endpointKey]->outputSingleItem($itemId);
    }

    /**
     * Defines the action parameter of ajax calls.
     *
     * @since  1.0.0
     * @return string action parameter to use for ajax calls
     */
    public function ajaxActionGetItem(): string
    {
        return $this->configuration['PLUGIN_PREFIX']  . '_get_item';
    }

    /**
     * Returns the data received by the ajax call.
     *
     * This method has been implemented to allow partial mocking for
     * unit tests.
     *
     * @since  1.0.0
     * @return array validated and sanitized ajax call data
     */
    protected function inputData(): array
    {
        return [
            filter_input(INPUT_GET, 'itemId', FILTER_VALIDATE_INT) ?? false,
            filter_input(INPUT_GET, 'endpoint', FILTER_SANITIZE_STRING) ?? '',
        ];
    }
}
