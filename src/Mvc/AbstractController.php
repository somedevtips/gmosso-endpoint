<?php
declare(strict_types=1);

/**
 * Base class for controllers
 *
 * Abstract representation of a Mvc controller.
 *
 * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
 *
 * @package Gmosso Endpoint
 * @since 1.0.0
 */

namespace GmossoEndpoint\Mvc;

use GmossoEndpoint\BootstrappableInterface;
use GmossoEndpoint\Configuration;
use GmossoEndpoint\Mvc\Data\AbstractData;
use GmossoEndpoint\Mvc\View\AbstractView;

abstract class AbstractController implements BootstrappableInterface
{
    protected string $moduleName = 'Name not defined';
    private string $pluginDir;
    protected Configuration $configuration;
    protected AbstractModel $model;

    /**
     * Class constructor.
     *
     * @since 1.0.0
     * @param AbstractModel $model         instance of the model
     * @param string        $pluginDir     root dir of this plugin
     * @param Configuration $configuration Configuration instance
     */
    public function __construct(
        AbstractModel $model,
        string $pluginDir,
        Configuration $configuration
    ) {

        $this->model = $model;
        $this->pluginDir = $pluginDir;
        $this->configuration = $configuration;
    }

    /**
     * Operations to execute at plugin bootstrap.
     *
     * @since  1.0.0
     * @return void
     */
    public function bootstrap(): void
    {
        $this->defineModuleName();
        $this->defineHooks();
    }

    /**
     * Sets the name of the current module.
     *
     * @since  1.0.0
     * @return void
     */
    abstract public function defineModuleName(): void;

    /**
     * Defines the callback functions for the hooks managed by this controller.
     *
     * @since  1.0.0
     * @return void
     */
    public function defineHooks(): void
    {
        $prefix = $this->configuration['PLUGIN_PREFIX'];
        $module = $this->moduleName;

        add_filter("{$prefix}_{$module}_title", [$this, 'pageTitle']);
        add_filter("{$prefix}_{$module}_content", [$this, 'allItemsHtml']);
    }

    /**
     * Returns the title to use for the page managed by current controller.
     *
     * @since  1.0.0
     * @param  string $title default title
     * @return string        custom title for this page
     */
    abstract public function pageTitle(string $title): string;

    /**
     * Returns the template file that displays all the items.
     *
     * @since  1.0.0
     * @return string template file full path
     */
    public function allItemsTemplate(): string
    {
        $templateDir = $this->configuration['TEMPLATE_DIR'];
        $templateName = $this->configuration['ENDPOINTS'][$this->moduleName]['template'];
        return $this->pluginDir .
            "/{$templateDir}/" .
            $templateName;
    }

    /**
     * Returns the html representation of the view that displays all items.
     *
     * @since  1.0.0
     * @param  string $dataHtml Default html representation
     * @return string           html representation of all items
     */
    public function allItemsHtml(string $dataHtml): string
    {
        $allItems = $this->model->allItems();
        return $this->view($allItems)->html();
    }

    /**
     * Outputs the representation of a single item.
     *
     * @since  1.0.0
     * @param  int    $itemId id of the item
     * @return void
     */
    public function outputSingleItem(int $itemId): void
    {
        $data = $this->model->singleItem($itemId);
        $this->view($data)->renderJson();
    }

    /**
     * Outputs the representation of an error.
     *
     * @since  1.0.0
     * @param  string $errorString description of the error
     * @return void
     */
    public function outputError(string $errorString): void
    {
        $data = $this->model->errorData($errorString);
        $this->view($data)->renderJson();
    }

    /**
     * View factory for the data to represent
     *
     * @since  1.0.0
     * @param  AbstractData $viewData the data to represent
     * @return AbstractView           the view to use
     */
    abstract protected function view(AbstractData $viewData): AbstractView;
}
