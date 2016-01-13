<?php
namespace Shopware\Plugins\ShopwareClockwork\Subscriber;

use Clockwork\Clockwork;
use Clockwork\Storage\FileStorage;
use Enlight\Event\SubscriberInterface;
use Shopware\Plugins\ShopwareClockwork\Clockwork\DataSource\ShopwareDataSource;

class Container implements SubscriberInterface
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
            'Enlight_Bootstrap_InitResource_shopwareclockwork.clockwork' => 'onClockworkService',
            'Enlight_Bootstrap_InitResource_shopwareclockwork.datasource' => 'onClockworkDataSourceService'
        ];
    }

    /**
     * @return Clockwork
     */
    public function onClockworkService()
    {
        $clockwork = new Clockwork();
        $clockwork->setStorage(new FileStorage($this->getClockworkLogPath()));
        return $clockwork;
    }

    /**
     * @return ShopwareDataSource
     */
    public function onClockworkDataSourceService()
    {
        return new ShopwareDataSource();
    }

    /**
     * @return string
     */
    public function getClockworkLogPath()
    {
        return $this->container->get('kernel')->getLogDir() . DIRECTORY_SEPARATOR . 'clockwork' . DIRECTORY_SEPARATOR;
    }
}
