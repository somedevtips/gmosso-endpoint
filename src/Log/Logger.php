<?php
declare(strict_types=1);

/**
 * Logging class
 *
 * Implementation of a simple logger.
 *
 * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\Log;

use Psr\Log\AbstractLogger;

class Logger extends AbstractLogger
{

    /**
    /**
     * Logs with an arbitrary level.
     *
     * @param mixed   $level
     * @param string  $message message to log
     * @param array $context {
     *     Optional. Information on context where logging was called.
     *         @type \Exception $exception In case of exception logging
     *         @type string $method Method where the logging was called
     *         @type string $line Line where the logging was called
     * }
     *
     * @return void
     *
     * Disable the following rules because this extends the external class
     * Psr\Log\AbstractLogger
     *
     * phpcs:disable Inpsyde.CodeQuality.ArgumentTypeDeclaration.NoArgumentType
     * phpcs:disable NeutronStandard.Arrays.DisallowLongformArray.LongformArray
     */
    public function log($level, $message, array $context = array())
    {
        // phpcs:enable
        // error_log not called in production because the NullLogger is used
        // phpcs:disable WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log(
            strtoupper($level) .
            $this->messageWithContext($message, $context)
        );
        // phpcs:enable
    }

    /**
     * Creates the string to log.
     *
     * @since  1.0.0
     * @see self::log
     * @return string Error string to log.
     */
    protected function messageWithContext(string $message, array $context): string
    {
        $info = ' (' . __CLASS__ . '): ';

        if (count($context) === 0) {
            return $info . $message;
        }

        if (array_key_exists('exception', $context)) {
            $exception = $context['exception'];
            if ($exception instanceof \Exception) {
                return $info . (string)$exception . ": $message";
            }
        }

        $info .= "{method}({line}) : $message";

        $replace = [
            '{method}' => '',
            '{line}' => '',
        ];
        foreach ($context as $key => $value) {
            // check that the value can be casted to string
            if (!is_array($value) &&
                (!is_object($value) || method_exists($value, '__toString'))) {
                $replace['{' . $key . '}'] = $value;
            }
        }

        return strtr($info, $replace);
    }
}
