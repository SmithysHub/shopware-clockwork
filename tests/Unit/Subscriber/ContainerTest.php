<?php

namespace Shopware\Tests\ShopwareClockwork\Unit\Subscriber;
use Clockwork\Clockwork;
use Shopware\Plugins\ShopwareClockwork\Subscriber\Container;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class ContainerTest extends \Enlight_Components_Test_TestCase
{


    public function testGetSubscribedEvents()
    {
        $subscribers = $this->getContainerClass()->getSubscribedEvents();
        $containerClass = $this->getContainerClass();

        foreach ($subscribers as $subscriberName => $subscriberMethod) {
            $this->assertTrue( strpos($subscriberName, 'Enlight_Bootstrap_InitResource_shopwareclockwork.') !== false  );
            $this->assertTrue( method_exists($containerClass, $subscriberMethod));
        }
    }


    public function testOnClockworkService()
    {
        /** @var Clockwork $clockworkClass */
        $clockworkClass = $this->getContainerClass()->onClockworkService();

        $this->assertInstanceOf('\Clockwork\Clockwork', $clockworkClass);
        $this->assertInstanceOf('\Clockwork\Storage\FileStorage', $clockworkClass->getStorage());
    }

    public function testOnClockworkDataSourceService()
    {
        $this->assertInstanceOf(
            '\Shopware\Plugins\ShopwareClockwork\Clockwork\DataSource\ShopwareDataSource',
            $this->getContainerClass()->onClockworkDataSourceService()
        );
    }


    public function testGetClockworkLogPath() {
        $clockworkLogPath = $this->getContainerClass()->getClockworkLogPath();

        $this->assertTrue( strpos($clockworkLogPath, 'log' . DIRECTORY_SEPARATOR . 'clockwork') !== false  );
    }


    protected function getContainerClass() {
        return new Container();
    }

}