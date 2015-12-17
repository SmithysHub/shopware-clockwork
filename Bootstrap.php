<?php

use Shopware\Plugins\ShopwareClockwork\Subscriber\Container;

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
    }


}