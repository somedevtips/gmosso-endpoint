<?php
declare(strict_types=1);

/**
 * Mvc model for the Users module.
 *
 * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\Users;

use GmossoEndpoint\Mvc\Data\AbstractData;
use GmossoEndpoint\Mvc\AbstractModel;

class Model extends AbstractModel
{
    protected string $modelKey = 'users';

    /**
     * @inheritDoc
     */
    protected function allItemsData(array $items): AbstractData
    {
        return new AllUsersData($items);
    }

    /**
     * @inheritDoc
     */
    protected function singleItemData(array $data): AbstractData
    {
        return new SingleUserData($data);
    }
}
