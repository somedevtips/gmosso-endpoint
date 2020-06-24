<?php
declare (strict_types = 1);

namespace GmossoEndpoint\Tests\Installation;

use GmossoEndpoint\Tests\GmossoEndpointTestCase;

use GmossoEndpoint\Configuration;
use GmossoEndpoint\Installation\Installer;
use GmossoEndpoint\Uninstallation\Uninstaller;
use GmossoEndpoint\Users\Model as UserModel;

use Brain\Monkey\Functions;
use Mockery;

class InstallerTest extends GmossoEndpointTestCase
{
    protected array $models;
    protected UserModel $userModel;
    protected string $pluginFile;
    protected Uninstaller $uninstaller;
    protected Configuration $configuration;
    protected Installer $installer;

    public const MOCKED_OPTION_NAME_VALUE = 'mocked_option_name';
    public const MOCKED_OPTION_INSTALLED_VALUE = 'mocked_installed';
    public const MOCKED_OPTION_TRANSIENTS_VALUE = 'mocked_transients';

    protected function setUp(): void
    {
        parent::setUp();

        $this->userModel =
            Mockery::mock('\GmossoEndpoint\Users\Model');
        $this->models = [$this->userModel];

        $this->pluginFile = __FILE__;

        $this->uninstaller =
            Mockery::mock('\GmossoEndpoint\Uninstallation\Uninstaller');

        $this->configuration = Mockery::mock(
            'GmossoEndpoint\Configuration'
        );

        $this->installer = new Installer(
            $this->models,
            $this->pluginFile,
            $this->uninstaller,
            $this->configuration
        );
    }

    public function testBootstrapAllHooks()
    {
        $this->installer->bootstrap();

        $this->assertTrue(
            has_action(
                'wp_loaded',
                'GmossoEndpoint\Installation\Installer->afterInstall()'
            )
        );

        $this->assertTrue(
            has_action(
                'upgrader_process_complete',
                'GmossoEndpoint\Installation\Installer->manageUpgrade()'
            )
        );
    }

    public function testReturnsImmediatelyIfAlreadyInstalled()
    {
        $this->configuration->shouldReceive('offsetGet')
            ->with('OPTION_NAME')
            ->andReturn(self::MOCKED_OPTION_NAME_VALUE);

        $this->configuration->shouldReceive('offsetGet')
            ->with('OPTION_INSTALLED')
            ->andReturn(self::MOCKED_OPTION_INSTALLED_VALUE);

        $pluginOptions = [self::MOCKED_OPTION_INSTALLED_VALUE => 1];

        Functions\expect('get_option')
            ->with(self::MOCKED_OPTION_NAME_VALUE, false)
            ->andReturn($pluginOptions);

        $this->installer->afterInstall();
    }

    public function testAfterInstallCompleteIfNotYetInstalled()
    {
        $this->configuration->shouldReceive('offsetGet')
            ->with('OPTION_NAME')
            ->andReturn(self::MOCKED_OPTION_NAME_VALUE);

        Functions\expect('get_option')
            ->with(self::MOCKED_OPTION_NAME_VALUE, false)
            ->andReturn(false);

        $this->configuration->shouldReceive('offsetGet')
            ->with('OPTION_INSTALLED')
            ->andReturn(self::MOCKED_OPTION_INSTALLED_VALUE);

        Functions\expect('flush_rewrite_rules')
            ->withNoArgs();

        $transients = [];
        foreach ($this->models as $model) {
            $transients[] = "mocked_transient_{$model}";
        }
        $this->userModel->shouldReceive('transientKey')
            ->times(count($this->models))
            ->withNoArgs()
            ->andReturnValues($transients);

        $this->configuration->shouldReceive('offsetGet')
            ->with('OPTION_TRANSIENTS')
            ->andReturn(self::MOCKED_OPTION_TRANSIENTS_VALUE);

        $pluginOptions = [
            self::MOCKED_OPTION_INSTALLED_VALUE => 1,
            self::MOCKED_OPTION_TRANSIENTS_VALUE => $transients,
        ];

        Functions\expect('update_option')
            ->with(self::MOCKED_OPTION_NAME_VALUE, $pluginOptions);

        $this->installer->afterInstall();
    }

    public function testManageUpgradeEndsIfUpdateIsOnlyForCore()
    {
        $pluginBasename = basename(dirname($this->pluginFile)) .
            '/' . basename($this->pluginFile);

        Functions\expect('plugin_basename')
            ->with($this->pluginFile)
            ->andReturn($pluginBasename);

        $options = ['action' => 'update', 'type' => 'core'];

        $this->installer->manageUpgrade(new \stdClass(), $options);
    }

    public function testManageUpgradeEndsIfUpdateIsForAnotherPlugin()
    {
        $pluginBasename = basename(dirname($this->pluginFile)) .
            '/' . basename($this->pluginFile);

        Functions\expect('plugin_basename')
            ->with($this->pluginFile)
            ->andReturn($pluginBasename);

        $options = [
            'action' => 'update',
            'type' => 'plugin',
            'plugins' => [__FILE__],
        ];

        $this->installer->manageUpgrade(new \stdClass(), $options);
    }

    public function testManageUpgradeExecutedIfPluginUpdatedButWithOptionNotSet()
    {
        $pluginBasename = basename(dirname($this->pluginFile)) .
            '/' . basename($this->pluginFile);

        Functions\expect('plugin_basename')
            ->with($this->pluginFile)
            ->andReturn($pluginBasename);

        $options = [
            'action' => 'update',
            'type' => 'plugin',
            'plugins' => [__FILE__, $pluginBasename],
        ];

        $this->configuration->shouldReceive('offsetGet')
            ->with('OPTION_NAME')
            ->andReturn(self::MOCKED_OPTION_NAME_VALUE);

        Functions\expect('get_option')
            ->with(self::MOCKED_OPTION_NAME_VALUE, false)
            ->andReturn(false);

        $this->installer->manageUpgrade(new \stdClass(), $options);
    }

    public function testManageUpgradeExecutedIfPluginUpdatedAndCorrectlyInstalled()
    {
        $pluginBasename = basename(dirname($this->pluginFile)) .
            '/' . basename($this->pluginFile);

        Functions\expect('plugin_basename')
            ->with($this->pluginFile)
            ->andReturn($pluginBasename);

        $options = [
            'action' => 'update',
            'type' => 'plugin',
            'plugins' => [__FILE__, $pluginBasename],
        ];

        $this->configuration->shouldReceive('offsetGet')
            ->with('OPTION_NAME')
            ->andReturn(self::MOCKED_OPTION_NAME_VALUE);

        $pluginOptions = [
            self::MOCKED_OPTION_INSTALLED_VALUE => 1,
        ];

        Functions\expect('get_option')
            ->with(self::MOCKED_OPTION_NAME_VALUE, false)
            ->andReturn($pluginOptions);

        $this->configuration->shouldReceive('offsetGet')
            ->with('OPTION_INSTALLED')
            ->andReturn(self::MOCKED_OPTION_INSTALLED_VALUE);

        $pluginOptions[self::MOCKED_OPTION_INSTALLED_VALUE] = 0;

        Functions\expect('update_option')
            ->with(self::MOCKED_OPTION_NAME_VALUE, $pluginOptions);

        $this->uninstaller->shouldReceive('deleteTransients')
            ->once()
            ->withNoArgs();

        $this->installer->manageUpgrade(new \stdClass(), $options);
    }
}
