<?php
declare(strict_types=1);

/**
 * Users module controller
 *
 * Implementation of Mvc AbstractController for the Users module.
 *
 * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\Users;

use GmossoEndpoint\Mvc\AbstractController;
use GmossoEndpoint\Mvc\Data\AbstractData;
use GmossoEndpoint\Mvc\View\AbstractView;
use GmossoEndpoint\Mvc\View\ErrorView;

class Controller extends AbstractController
{
    /**
     * @inheritDoc
     */
    public function pageTitle(string $title): string
    {
        return __('All users', 'gmosso-endpoint');
    }

    /**
     * @inheritDoc
     */
    public function defineModuleName(): void
    {
        $this->moduleName = 'users';
    }

    /**
     * @inheritDoc
     */
    protected function view(AbstractData $viewData): AbstractView
    {
        if ($viewData instanceof AllUsersData) {
            return new AllUsersView($viewData);
        }
        if ($viewData instanceof SingleUserData) {
            return new SingleUserView($viewData);
        }
        return new ErrorView($viewData);
    }
}
