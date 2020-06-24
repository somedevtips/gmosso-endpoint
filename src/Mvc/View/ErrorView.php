<?php
declare(strict_types=1);

/**
 * View for an error
 *
 * View that renders an error condition.
 *
 * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\Mvc\View;

class ErrorView extends AbstractView
{
    /**
     * @inheritDoc
     */
    public function html(): string
    {
        $errors = $this->viewData->data();

        $errorHtml = '';
        foreach ($errors as $error) {
            $errorHtml .= '<p class="error">';
            $errorHtml .= sprintf(
                /* translators: %s: string that describes the error */
                __(
                    'Error: %s',
                    'gmosso-endpoint'
                ),
                $error
            );
            $errorHtml .= '</p>';
        }

        return $errorHtml;
    }

    /**
     * @inheritDoc
     */
    public function renderJson(): void
    {
        wp_send_json_error($this->prepareJsonOutput(), 400);
    }
}
