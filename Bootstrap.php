<?php

use Clockwork\Clockwork;
use Clockwork\DataSource\PhpDataSource;
use Shopware\Plugin\Debug\Components\TemplateVarCollector;
use Shopware\Plugins\ShopwareClockwork\Subscriber\Container;

class Shopware_Plugins_Core_ShopwareClockwork_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var CollectorInterface[]
     */
    protected $collectors = [];

    /**
     * The afterInit function registers the custom plugin models.
     */
    public function afterInit()
    {
        $this->get('Loader')->registerNamespace(
            'Shopware\\Plugins\\' . basename(__DIR__) ,
            $this->Path()
        );
        require_once __DIR__ . '/vendor/autoload.php';
    }

    /**
     * Gibt die Informationen zum Plugin zurueck
     * @return array
     */
    public function getInfo()
    {
        return array(
            'version' => $this->getVersion(),
            'label' => $this->getLabel(),
            'description' => 'Clockwork for Shopware',
            'link' => 'https://github.com/wesolowski/',
            'author' => 'Rafal Wesolowski'
        );
    }

    /**
     * Gibt die Version des Plugin zurueck
     * @return string
     */
    public function getVersion()
    {
        return "dev";
    }

    /**
     * Der Name des Plugins
     * @return string
     */
    public function getLabel()
    {
        return 'Clockwork';
    }

    /**
     * Installiert das Plugin
     * @return bool
     * @throws Exception
     */
    public function install()
    {
        $this->subscribeEvent('Enlight_Controller_Front_StartDispatch', 'onStartDispatch');
        $this->registerController('Frontend', 'Clockwork');

        $clockWorkLog = (new Container())->getClockworkLogPath();
        if( ! is_dir($clockWorkLog) ) {
            mkdir($clockWorkLog, 0755);
        }

        return true;
    }

    /**
     * register all subscriber class for dynamic event subsciption without plugin reinstallation
     */
    public function onStartDispatch()
    {
        $events = $this->Application()->Events();

        $subscribers = [
            new Container(),
        ];

        foreach ($subscribers as $subscriber) {
            $events->addSubscriber($subscriber);
        }


        $this->registerCollectors();

        $this->get('events')->addListener(
            'Enlight_Controller_Front_DispatchLoopShutdown',
            array($this, 'onDispatchLoopShutdown')
        );

    }

    /**
     * Registeres active collectors
     */
    public function registerCollectors()
    {
        $eventManager = $this->get('events');


        $this->collectors[] = (new TemplateVarCollector($eventManager));

        foreach ($this->collectors as $collector) {
            $collector->start();
        }
    }


    /**
     * Listener method of the Enlight_Controller_Front_DispatchLoopShutdown event.
     * On Dispatch Shutdown collects results and dumps to log component.
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onDispatchLoopShutdown(\Enlight_Event_EventArgs $args)
    {
//        foreach ($this->collectors as $collector) {
//            $collector->logResults($this->getLogger());
//        }
        /** @var Clockwork $clockwork */
        $clockwork = Shopware()->Container()->get('shopwareclockwork.clockwork');

        $args->getResponse()->setHeader("X-Clockwork-Id", $clockwork->getRequest()->id);
        $args->getResponse()->setHeader("X-Clockwork-Version",  $clockwork::VERSION);
        $args->getResponse()->setHeader("X-Clockwork-Path",  '/Clockwork/index/id/');
        $clockwork->addDataSource(new PhpDataSource());
        $clockwork->resolveRequest();
        $clockwork->storeRequest();
    }

}