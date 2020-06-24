<?php
declare(strict_types=1);

/**
 * Rest api data provider implementation
 *
 * Implements a data provider that reads from a rest api.
 *
 * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\DataProvider;

use Psr\Log\AbstractLogger;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;

use GmossoEndpoint\Configuration;

class RestApiDataProvider extends AbstractDataProvider
{
    /**
     * @inheritDoc
     *
     * @throws RestApiException if the rest api returns an error.
     */
    public function readData(string $endpoint): array
    {
        $this->logger->info(__METHOD__ . ' called');

        $url = $this->configuration['API_ROOT'] . "/$endpoint";

        $response = wp_remote_get($url);
        // Check response
        if (is_wp_error($response)) {
            // print_r with second parameter=true does not echo and its result is
            // not logged in production because the NullLogger is used
            //
            // phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_print_r
            $this->logger->error(
                print_r($response->get_error_messages(), true),
                ['method' => __METHOD__, 'line' => __LINE__]
            );
            //phpcs:enable

            throw new RestApiException(__(
                'no reply from server',
                'gmosso-endpoint'
            ));
        }

        $data = $this->validateResponse($response);

        $this->processCacheInfo($response);

        return $data;
    }

    /**
     * Extracts the cache information from the rest api reply.
     *
     * @since  1.0.0
     * @param  array  $response raw data sent by the rest api
     * @return void
     */
    protected function processCacheInfo(array $response): void
    {
        $cacheControl = wp_remote_retrieve_header($response, 'cache-control');

        if ($cacheControl === '') {
            return;
        }

        // Get cache-control directives
        $directives = [];
        $directivesAndValues = explode(',', $cacheControl);
        foreach ($directivesAndValues as $directiveAndValue) {
            $directive = explode('=', $directiveAndValue);

            $directiveName = $directive[0] ?? null;

            if ($directiveName) {
                $directives[trim($directiveName)] =
                    isset($directive[1]) ? trim($directive[1]): null;
            }
        }

        $directiveNames = array_keys($directives);

        // To simplify for the moment we consider no-cache like no-store, even if
        // no-cache does not mean that it's not cacheable:
        // https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Cache-Control
        $noCacheableDirectives = ['private', 'no-cache', 'no-store'];
        if (count(array_intersect(
            $noCacheableDirectives,
            $directiveNames
        )) > 0) {
            $this->cacheable = false;
            return;
        }

        // Get max-age parameter
        $maxAge = 0;
        if (in_array('max-age', $directiveNames, true) &&
            is_numeric($directives['max-age'])) {
            $maxAge = (int)$directives['max-age'];
        }
        if ($maxAge > 0) {
            $this->cacheable = true;
            $this->cacheTtl = $maxAge;
        }
    }

    /**
     * Checks the validity of the response from the rest api.
     *
     * @since  1.0.0
     * @param  array  $response raw data sent by the rest api
     * @return array            data validated and parsed
     * @throws RestApiException if the rest api returns an error or data are not
     *                          valid json
     */
    protected function validateResponse(array $response): array
    {
        // Validate http return code
        $httpCode = wp_remote_retrieve_response_code($response);
        if ($httpCode !== 200) {
            $this->logger->error(
                "unexpected http return code $httpCode",
                ['method' => __METHOD__, 'line' => __LINE__]
            );

            throw new RestApiException(__(
                'server replied with an error',
                'gmosso-endpoint'
            ));
        }

        // Validate response is json
        $body = wp_remote_retrieve_body($response);
        try {
            $jsonAssoc = $this->jsonParser->parse($body, JsonParser::PARSE_TO_ASSOC);
        } catch (ParsingException $exc) {
            $this->logger->error(
                "api call returned invalid json",
                ['exception' => $exc]
            );

            throw new RestApiException(__(
                'server replied with invalid format',
                'gmosso-endpoint'
            ));
        }

        return $jsonAssoc;
    }
}
