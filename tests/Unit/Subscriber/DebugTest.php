<?php

namespace Shopware\Tests\ShopwareClockwork\Unit\Subscriber;

use Shopware\Plugin\Debug\Components\EventCollector;
use Shopware\Plugin\Debug\Components\TemplateCollector;
use Shopware\Plugin\Debug\Components\Utils;
use Shopware\Plugins\ShopwareClockwork\Subscriber\Debug;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class DebugTest extends \Enlight_Components_Test_TestCase
{

    public function testGetSubscribedEvents()
    {
        $subscribers = $this->getDebugClass()->getSubscribedEvents();
        $debugClass = $this->getDebugClass();

        foreach ($subscribers as $subscriberName => $subscriberMethod) {
            $this->assertTrue( strpos($subscriberName, 'Shopware_Plugins_Core_Debug_Bootstrap_Filter') !== false  );
            $this->assertTrue( method_exists($debugClass, $subscriberMethod));
        }
    }

    public function testOnFilterHandlerRegister()
    {
        $debug = $this->getDebugClass();
        $this->assertInstanceOf('\Shopware\Plugins\ShopwareClockwork\Clockwork\Components\Log\ClockworkHandler', $debug->onFilterHandlerRegister());
    }

    public function testOnFilterCollectors()
    {
        $container = Shopware()->Container();
        $debug = $this->getDebugClass();
        $eventManager = $container->get('events');
        $utils = new Utils();

        $arguments = new \Enlight_Event_EventArgs([
            'utils' => $utils,
            'eventManager' => $eventManager
        ]);
        $arguments->setReturn([]);

        $collectors = $debug->onFilterCollectors($arguments);
        $this->assertCount(count($arguments->getReturn()), $collectors);

        $arguments->setReturn([
            new EventCollector($eventManager, $utils)
        ]);

        $collectors = $debug->onFilterCollectors($arguments);
        $this->assertCount(count($arguments->getReturn()), $collectors);
        $this->assertInstanceOf('\Shopware\Plugins\ShopwareClockwork\Clockwork\Components\Collector\EventCollector', $collectors[0]);

        $arguments->setReturn([
            new TemplateCollector($container->get('template'), $utils, $container->get('kernel')->getRootDir())
        ]);

        $collectors = $debug->onFilterCollectors($arguments);
        $this->assertCount(count($arguments->getReturn()), $collectors);
        $this->assertInstanceOf('\Shopware\Plugins\ShopwareClockwork\Clockwork\Components\Collector\TemplateCollector', $collectors[0]);

        $arguments->setReturn([
            new EventCollector($eventManager, $utils),
            new TemplateCollector($container->get('template'), $utils, $container->get('kernel')->getRootDir())
        ]);

        $collectors = $debug->onFilterCollectors($arguments);
        $this->assertCount(count($arguments->getReturn()), $collectors);
        $this->assertInstanceOf('\Shopware\Plugins\ShopwareClockwork\Clockwork\Components\Collector\EventCollector', $collectors[0]);
        $this->assertInstanceOf('\Shopware\Plugins\ShopwareClockwork\Clockwork\Components\Collector\TemplateCollector', $collectors[1]);

        $info = [
             1, 2, 3
        ];
        $arguments->setReturn($info);
        $collectors = $debug->onFilterCollectors($arguments);
        $this->assertCount(count($arguments->getReturn()), $collectors);
        $this->assertSame($info, $collectors);
    }

    protected function getDebugClass() {
        return new Debug();
    }

}