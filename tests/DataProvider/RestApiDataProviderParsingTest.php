<?php
declare (strict_types = 1);

namespace GmossoEndpoint\Tests\DataProvider;

use GmossoEndpoint\Tests\GmossoEndpointTestCase;

class RestApiDataProviderParsingTest extends GmossoEndpointTestCase
{
    use RestApiCommonSetupTrait;
    use RestApiReadDataOKExpectationsTrait;

    protected function setUp(): void
    {
        $this->commonSetup();
    }

    public function testParseAllUsersCorrect()
    {
        $usersData = file_get_contents(__DIR__ . '/../users.json');
        $response = $this->simpleResponse();
        $response['body'] = $usersData;

        $this->expectationsForSuccessfulReadData($response);

        $users = $this->dataProvider->readData($this->endpoint);

        $this->assertIsArray($users);
        $this->assertCount(10, $users);
        $this->assertSame($users[1]['phone'], "010-692-6593 x09125");
    }

    public function testParseEmptyUserListCorrect()
    {
        $response = $this->simpleResponse();
        $response['body'] = '[]';

        $this->expectationsForSuccessfulReadData($response);

        $users = $this->dataProvider->readData($this->endpoint);

        $this->assertIsArray($users);
        $this->assertCount(0, $users);
    }
}
