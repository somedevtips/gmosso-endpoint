<?php
declare (strict_types = 1);

namespace GmossoEndpoint\Tests\Users;

use GmossoEndpoint\Mvc\Data\AbstractData;
use GmossoEndpoint\Mvc\Data\ErrorData;
use GmossoEndpoint\Users\AllUsersData;
use GmossoEndpoint\Users\SingleUserData;

class TestData
{
    protected array $users;

    public function __construct()
    {
        $this->users = json_decode(
            file_get_contents(__DIR__ . '/../users.json'),
            true
        );
    }

    public function allUsersData(): AllUsersData
    {
        return new AllUsersData($this->users);
    }

    public function allUsersDataWithMissingName(): AllUsersData
    {
        $users = $this->users;
        unset($users[1]['name']);
        return new AllUsersData($users);
    }

    public function allUsersDataWithMissingId(): AllUsersData
    {
        $users = $this->users;
        unset($users[1]['id']);
        return new AllUsersData($users);
    }

    public function singleUserData(int $itemId): AbstractData
    {
        if (isset($this->users[$itemId])) {
            return new SingleUserData($this->users[$itemId]);
        }

        return new ErrorData(['user not found']);
    }

    public function errorData(): AbstractData
    {
        return new ErrorData(['this is the error description']);
    }

    public function maxIdAllUsers(): int
    {
        return max(array_values(array_column($this->users, 'id')));
    }
}
