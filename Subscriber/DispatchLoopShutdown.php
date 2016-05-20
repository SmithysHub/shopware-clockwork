<?php
namespace Shopware\Plugins\ShopwareClockwork\Subscriber;

use Clockwork\Clockwork;
use Clockwork\DataSource\PhpDataSource;
use Enlight\Event\SubscriberInterface;

class DispatchLoopShutdown implements SubscriberInterface
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
            'Enlight_Controller_Front_DispatchLoopShutdown' => [ 'onDispatchLoopShutdown', 10000 ],
        ];
    }

    /**
     * Listener method of the Enlight_Controller_Front_DispatchLoopShutdown event.
     * On Dispatch Shutdown collects results and dumps to log component.
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onDispatchLoopShutdown(\Enlight_Event_EventArgs $args)
    {

        /** @var \Enlight_Controller_Request_Request $request */
        $request = $args->getSubject()->Request();

        /** @var Clockwork $clockwork */
        $clockwork = $this->container->get('shopwareclockwork.clockwork');
        /** @var \Enlight_Controller_Response_ResponseHttp $response */
        $response = $args->getResponse();


        $response->setHeader("X-Clockwork-Id", $clockwork->getRequest()->id);
        $response->setHeader("X-Clockwork-Version",  $clockwork::VERSION);
        $response->setHeader("X-Clockwork-Path",  '/Clockwork/index/id/');

        $shopwareDataSource = $this->container->get('shopwareclockwork.datasource');
        $shopwareDataSource->setBaseInfo($request);
        $clockwork->addDataSource(new PhpDataSource());
        $clockwork->addDataSource($shopwareDataSource);

        $clockwork->resolveRequest();
        $clockwork->storeRequest();
    }
}
