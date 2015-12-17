<?php
namespace Shopware\Plugins\ShopwareClockwork\Subscriber;

use Clockwork\Clockwork;
use Clockwork\Storage\FileStorage;
use Enlight\Event\SubscriberInterface;

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
            'Enlight_Bootstrap_InitResource_shopwareclockwork.clockwork' => 'onClockworkService'
        ];
    }

    /**
     * @return Clockwork
     */
    public function onClockworkService()
    {
        $clockwork = new Clockwork();
        //@ToDo Check if Allowed -> NullStorage
        $clockwork->setStorage(new FileStorage($this->getClockworkLogPath()));
        return $clockwork;
    }

    /**
     * @return string
     */
    public function getClockworkLogPath() {
        return $this->container->get('kernel')->getLogDir() . DIRECTORY_SEPARATOR . 'clockwork' . DIRECTORY_SEPARATOR;
    }

}