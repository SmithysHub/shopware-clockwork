<?php
namespace Shopware\Plugins\ShopwareClockwork\Clockwork;
use Shopware\Components\Logger;
use Shopware\Plugin\Debug\Components\CollectorInterface;
use Shopware\Plugin\Debug\Components\ControllerCollector;
use Shopware\Plugin\Debug\Components\DatabaseCollector;
use Shopware\Plugin\Debug\Components\DbalCollector;
use Shopware\Plugin\Debug\Components\ErrorCollector;
use Shopware\Plugin\Debug\Components\ExceptionCollector;
use Shopware\Plugin\Debug\Components\TemplateVarCollector;
use Shopware\Plugin\Debug\Components\Utils;

use Shopware\Plugins\ShopwareClockwork\Clockwork\Components\Collector\TemplateCollector;
use Shopware\Plugins\ShopwareClockwork\Clockwork\Components\Collector\EventCollector;
use Shopware\Plugins\ShopwareClockwork\Clockwork\Components\Log\ClockworkHandler;

/**
 * @deprecated wait for merge pull request https://github.com/shopware/shopware/pull/404
 * @package Shopware\Plugins\ShopwareClockwork\Clockwork
 */
class Setup
{
    /**
     * @var \Enlight_Config
     */
    private $config;


    private $logger;

    /**
     * @var \Enlight_Loader
     */
    private $loader;

    /**
     * @var \Enlight_Plugin_PluginCollection
     */
    private $collection;

    /**
     * @var array
     */
    private $collectors = [];

    /**
     * Setup constructor.
     * @param $config
     * @param $loader
     * @param $collection
     */
    public function __construct($config, $loader, $collection)
    {
        $this->config = $config;
        $this->loader = $loader;
        $this->collection = $collection;
    }


    public function init() {
        $this->getLogger()->pushHandler(new ClockworkHandler());

        $this->registerCollectors();
    }


    /**
     * @param \Enlight_Controller_Request_Request $request
     * @return bool
     */
    public function isRequestAllowed(\Enlight_Controller_Request_Request $request)
    {
        $clientIp  = $request->getClientIp();
        $allowedIp = $this->config->get('AllowIP');

        if (empty($allowedIp)) {
            return true;
        }

        if (empty($clientIp)) {
            return false;
        }

        return (strpos($allowedIp, $clientIp) !== false);
    }

    private function registerCollectors()
    {
        $this->loader->registerNamespace('Shopware\Plugin\Debug', realpath(__DIR__ . '/../../../../Default/Core/Debug/') . '/');

        $eventManager = Shopware()->Container()->get('events');
        $utils = new Utils();
        $errorHandler = $this->collection->get('ErrorHandler');

        if ($this->Config()->get('logTemplateVars')) {
            $this->pushCollector(new TemplateVarCollector($eventManager));
        }

        if ($this->Config()->get('logErrors')) {
            $this->pushCollector(new ErrorCollector($errorHandler, $utils));
        }

        if ($this->Config()->get('logExceptions')) {
            $this->pushCollector(new ExceptionCollector($eventManager, $utils));
        }

        if ($this->Config()->get('logDb')) {
            $this->pushCollector(new DatabaseCollector(Shopware()->Container()->get('db')));
        }

        if ($this->Config()->get('logModel')) {
            $this->pushCollector(new DbalCollector(Shopware()->Container()->get('modelconfig')));
        }

        if ($this->Config()->get('logTemplate')) {
            $this->pushCollector(new TemplateCollector(Shopware()->Container()->get('template'), $utils, Shopware()->Container()->get('kernel')->getRootDir()));
        }

        if ($this->Config()->get('logController')) {
            $this->pushCollector(new ControllerCollector($eventManager, $utils));
        }

        if ($this->Config()->get('logEvents')) {
            $this->pushCollector(new EventCollector($eventManager, $utils));
        }

        foreach ($this->collectors as $collector) {
            $collector->start();
        }
    }

    /**
     * @return array
     */
    public function getCollectors()
    {
        return $this->collectors;
    }


    /**
     * @return Logger
     */
    public function getLogger()
    {
        if ($this->logger === null) {
            $this->logger = Shopware()->Container()->get('debuglogger');
        }

        return $this->logger;
    }

    /**
     * @param CollectorInterface $collector
     */
    private function pushCollector(CollectorInterface $collector)
    {
        $this->collectors[] = $collector;
    }

    private function Config() {
        return $this->config;
    }
}
