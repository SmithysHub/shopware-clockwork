<?php
namespace Shopware\Plugins\ShopwareClockwork\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Plugins\ShopwareClockwork\Clockwork\Components\Log\ClockworkHandler;
use Shopware\Plugins\ShopwareClockwork\Clockwork\Components\Collector\EventCollector as ClockworkEventCollector;
use Shopware\Plugins\ShopwareClockwork\Clockwork\Components\Collector\TemplateCollector as ClockworkTemplateCollector;
use Shopware\Plugin\Debug\Components\EventCollector;
use Shopware\Plugin\Debug\Components\TemplateCollector;

class Debug implements SubscriberInterface
{

    /**
     * @var \Shopware\Components\DependencyInjection\Container
     */
    protected $container;

    /**
     * Container constructor.
     */
    public function __construct()
    {
        $this->container = Shopware()->Container();
    }

    public static function getSubscribedEvents()
    {
        return [
            'Shopware_Plugins_Core_Debug_Bootstrap_FilterHandlerRegister' => 'onFilterHandlerRegister',
            'Shopware_Plugins_Core_Debug_Bootstrap_FilterCollectors' => 'onFilterCollectors'
        ];
    }

    public function onFilterHandlerRegister()
    {
        return new ClockworkHandler();
    }

    public function onFilterCollectors(\Enlight_Event_EventArgs $arguments)
    {
        $collectors = $arguments->getReturn();
        $utils = $arguments->get('utils');

        foreach ($collectors as $key => $collector) {
            if( $collector instanceof EventCollector) {
                $collectors[$key] = new ClockworkEventCollector(
                    $arguments->get('eventManager'),
                    $utils
                );
            } elseif( $collector instanceof TemplateCollector) {
                $collectors[$key] = new ClockworkTemplateCollector(
                    $this->container->get('template'),
                    $utils,
                    $this->container->get('kernel')->getRootDir()
                );
            }
        }

        return $collectors;
    }

}