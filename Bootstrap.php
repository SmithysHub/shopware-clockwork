<?php

use Shopware\Plugins\ShopwareClockwork\Clockwork\Components\ClockworkLogger;
use Shopware\Plugins\ShopwareClockwork\Clockwork\Components\Log\ClockworkHandler;
use Shopware\Plugins\ShopwareClockwork\Subscriber\Container;
use Shopware\Plugins\ShopwareClockwork\Subscriber\Debug;
use Shopware\Plugins\ShopwareClockwork\Subscriber\DispatchLoopShutdown;

class Shopware_Plugins_Core_ShopwareClockwork_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
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
        return "1.0 RC1";
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
        if ($this->isDebugPluginActive() === false ) {
            throw new Exception('"Shopware-Debug-plugin" is not active');
        }

        $this->subscribeEvent('Enlight_Controller_Front_StartDispatch', 'onStartDispatch', -1);
        $this->registerController('Frontend', 'Clockwork');

        $this->createClockworkLogDir();

        return true;
    }

    /**
     * register all subscriber class for dynamic event subsciption without plugin reinstallation
     *
     * @param \Enlight_Event_EventArgs $args
     */
    public function onStartDispatch(\Enlight_Event_EventArgs $args)
    {
        $events = $this->Application()->Events();
        $events->addSubscriber(new Container());

        if( $this->isDebugPluginActive() === false ) {
            return;
        }

        $subscribers = [
            new Debug()
        ];

        /** @var \Shopware_Plugins_Core_Debug_Bootstrap  $debugPlugin */
        $debugPlugin = Shopware()->Plugins()->Core()->Debug();
        /** @var \Enlight_Controller_Request_Request $request */
        $request = $args->getSubject()->Request();

        if( $debugPlugin->isRequestAllowed($request) === true && (new ClockworkHandler())->acceptsRequest($request) === true ) {
            $subscribers[] = new DispatchLoopShutdown();
        }

        foreach ($subscribers as $subscriber) {
            $events->addSubscriber($subscriber);
        }
    }

    protected function isDebugPluginActive() {
        return Shopware()->Plugins()->Core()->Debug()->Info()->get('active') === "1";
    }

    protected function createClockworkLogDir()
    {
        $clockWorkLog = (new Container())->getClockworkLogPath();
        if (!is_dir($clockWorkLog)) {
            mkdir($clockWorkLog, 0755);
        }
    }


}