<?php
declare (strict_types = 1);

namespace GmossoEndpoint\Tests\Users;

use GmossoEndpoint\Tests\GmossoEndpointTestCase;

use GmossoEndpoint\Mvc\AbstractModel;
use GmossoEndpoint\Configuration;
use GmossoEndpoint\Users\Controller;

use Mockery;
use Brain\Monkey\Functions;

class ControllerTest extends GmossoEndpointTestCase
{
    protected AbstractModel $model;
    protected string $pluginDir;
    protected Configuration $configuration;
    protected Controller $controller;
    protected string $moduleName = 'users';
    protected TestData $testData;

    public const MOCKED_PLUGIN_PREFIX_VALUE = 'mocked_plugin_prefix';
    public const MOCKED_TEMPLATE_DIR_VALUE = 'mocked_template_dir';
    public const MOCKED_ENDPOINTS_VALUE = [
        'users' => ['template' => 'template1.php'],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->model =
            Mockery::mock('\GmossoEndpoint\Users\Model');

        $this->pluginDir = __DIR__; //fake plugin dir

        $this->configuration = Mockery::mock(
            'GmossoEndpoint\Configuration'
        );

        $this->controller = new Controller(
            $this->model,
            $this->pluginDir,
            $this->configuration
        );

        $this->testData = new TestData();

        Functions\stubTranslationFunctions();
    }

    public function testBootstrapAllHooks()
    {
        $prefix = self::MOCKED_PLUGIN_PREFIX_VALUE;

        $this->configuration->shouldReceive('offsetGet')
            ->with('PLUGIN_PREFIX')
            ->andReturn($prefix);

        $this->controller->bootstrap();

        $this->assertTrue(
            has_filter(
                "{$prefix}_{$this->moduleName}_title",
                '\GmossoEndpoint\Users\Controller->pageTitle()'
            )
        );

        $this->assertTrue(
            has_filter(
                "{$prefix}_{$this->moduleName}_content",
                '\GmossoEndpoint\Users\Controller->allItemsHtml()'
            )
        );
    }

    public function testAllItemsTemplate()
    {
        $templateDir = self::MOCKED_TEMPLATE_DIR_VALUE;

        $this->configuration->shouldReceive('offsetGet')
            ->with('TEMPLATE_DIR')
            ->andReturn($templateDir);

        $this->controller->defineModuleName();

        $this->configuration->shouldReceive('offsetGet')
            ->with('ENDPOINTS')
            ->andReturn(self::MOCKED_ENDPOINTS_VALUE);

        $template = $this->controller->allItemsTemplate();

        $this->assertMatchesRegularExpression(
            '#^' . preg_quote($this->pluginDir) . "/{$templateDir}/" . '.+\.php$#',
            $template
        );
    }

    public function testAllUsersDataOutputsHtmlTable()
    {
        $data = $this->testData->allUsersData();

        $this->model->shouldReceive('allItems')
            ->withNoArgs()
            ->andReturn($data);

        $html = $this->controller->allItemsHtml('');

        $this->assertMatchesRegularExpression(
            '#^<div id="table-items">.+</div>$#',
            $html
        );
    }

    public function testViewOutputsTableEvenIfAUserFieldIsMissing()
    {
        $data = $this->testData->allUsersDataWithMissingName();

        $this->model->shouldReceive('allItems')
            ->withNoArgs()
            ->andReturn($data);

        $html = $this->controller->allItemsHtml('');

        $this->assertMatchesRegularExpression(
            '#^<div id="table-items">.+</div>$#',
            $html
        );
    }

    public function testViewOutputsTableEvenIfAUserIdIsMissing()
    {
        $data = $this->testData->allUsersDataWithMissingId();

        $this->model->shouldReceive('allItems')
            ->withNoArgs()
            ->andReturn($data);

        $html = $this->controller->allItemsHtml('');

        $this->assertMatchesRegularExpression(
            '#^<div id="table-items">.+</div>$#',
            $html
        );
    }

    public function testErrorDataOutputsErrorParagraph()
    {
        $data = $this->testData->errorData();

        $this->model->shouldReceive('allItems')
            ->withNoArgs()
            ->andReturn($data);

        $html = $this->controller->allItemsHtml('');

        $this->assertMatchesRegularExpression('#^<p class="error">.+</p>$#', $html);
    }

    public function testSingleUserFoundOutputsJson()
    {
        $itemId = 1;
        $userData = $this->testData->singleUserData($itemId);

        $this->model->shouldReceive('singleItem')
            ->with($itemId)
            ->andReturn($userData);

        Functions\expect('wp_send_json')
            ->withArgs([$this, 'validateJsonData']);

        $this->controller->outputSingleItem($itemId);
    }

    public function testSingleUserNotFoundOutputsJsonError()
    {
        $itemId = 11;
        $userData = $this->testData->singleUserData($itemId);

        $this->model->shouldReceive('singleItem')
            ->with($itemId)
            ->andReturn($userData);

        Functions\expect('wp_send_json_error')
            ->withArgs([$this, 'validateJsonData']);

        $this->controller->outputSingleItem($itemId);
    }

    public function testOutputError()
    {
        $errorString = 'error found';
        $errorData = $this->testData->errorData();

        $this->model->shouldReceive('errorData')
            ->with($errorString)
            ->andReturn($errorData);

        Functions\expect('wp_send_json_error')
            ->withArgs([$this, 'validateJsonData']);

        $this->controller->outputError($errorString);
    }

    protected function validateJsonData(array $argument, int $httpCode = null): bool
    {
        $checksOk = true;

        if (count($argument) !== 1 ||
            !array_key_exists('html', $argument)
        ) {
            $checksOk = false;
        }

        if (!is_null($httpCode) && $httpCode !== 400) {
            $checksOk = false;
        }

        return $checksOk;
    }
}
