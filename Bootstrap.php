<?php

use Shopware\Plugins\ShopwareClockwork\Clockwork\Components\ClockworkLogger;
use Shopware\Plugins\ShopwareClockwork\Clockwork\Components\Log\ClockworkHandler;
use Shopware\Plugins\ShopwareClockwork\Subscriber\Container;
use Shopware\Plugins\ShopwareClockwork\Subscriber\Debug;
use Shopware\Plugins\ShopwareClockwork\Subscriber\DispatchLoopShutdown;

use Shopware\Plugins\ShopwareClockwork\Clockwork\Setup;

class Shopware_Plugins_Core_ShopwareClockwork_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @var Setup
     */
    private $setup;

    /**
     * The afterInit function registers the custom plugin models.
     */
    public function afterInit()
    {
        $this->get('Loader')->registerNamespace(
            'Shopware\\Plugins\\' . basename(__DIR__),
            $this->Path()
        );

        if($this->hasVendorAutoloadFile() === true ) {
            require_once $this->getVendorAutoloadFile();
        }
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
       if ($this->hasVendorAutoloadFile() === false) {
           throw new Exception('Composer autoloader class not found.');
       }

        $this->subscribeEvent('Enlight_Controller_Front_StartDispatch', 'onStartDispatch', -1);
        $this->registerController('Frontend', 'Clockwork');

        $this->createClockworkLogDir();


        $form   = $this->Form();
        $parent = $this->Forms()->findOneBy(array('name' => 'Core'));
        $form->setParent($parent);
        $form->setElement('text', 'AllowIP', array('label' => 'Restrict to IP', 'value' => ''));
        $fields = array(
            array(
                'name'        => 'logTemplateVars',
                'label'       => 'Log template vars',
                'default'     => true,
            ),
            array(
                'name'        => 'logErrors',
                'label'       => 'Log errors',
                'default'     => true,
            ),
            array(
                'name'        => 'logExceptions',
                'label'       => 'Log exceptions',
                'default'     => true,
            ),
            array(
                'name'        => 'logDb',
                'label'       => 'Benchmark Zend_Db queries',
            ),
            array(
                'name'        => 'logModel',
                'label'       => 'Benchmark DBAL queries',
            ),
            array(
                'name'        => 'logTemplate',
                'label'       => 'Benchmark template',
            ),
            array(
                'name'        => 'logController',
                'label'       => 'Benchmark controller events',
            ),
            array(
                'name'        => 'logEvents',
                'label'       => 'Benchmark events',
            ),
        );

        foreach ($fields as $field) {
            $form->setElement('boolean', $field['name'], array(
                'label' => $field['label'],
                'value' => (isset($field['default'])) ?: false,
            ));
        }

        return true;
    }

    /**
     * @param Enlight_Event_EventArgs $args
     */
    public function onStartDispatch(\Enlight_Event_EventArgs $args)
    {
        $this->setup = new Setup(
            $this->Config(),
            $this->get('loader'),
            $this->Collection()
        );
        $events = $this->Application()->Events();
        $events->addSubscriber(new Container());

        $subscribers = [];
        // @toDo wait for merge pull request https://github.com/shopware/shopware/pull/404
        //        if ($this->isDebugPluginActive() === false) {
        //            return;
        //        }
        //        $subscribers[] = new Debug();

        /** @var \Shopware_Plugins_Core_Debug_Bootstrap  $debugPlugin */
        //        $debugPlugin = Shopware()->Plugins()->Core()->Debug();

        /** @var \Enlight_Controller_Request_Request $request */
        $request = $args->getSubject()->Request();
        if ( $this->setup->isRequestAllowed($request) === true && (new ClockworkHandler())->acceptsRequest($request) === true) {

            $this->setup->init();

            $events->addSubscriber(new DispatchLoopShutdown());

            $this->get('events')->addListener(
                'Enlight_Controller_Front_DispatchLoopShutdown',
                array($this, 'onDispatchLoopShutdown')
            );
        }
    }

    /**
     * @deprecated wait for merge pull request https://github.com/shopware/shopware/pull/404
     * @param \Enlight_Event_EventArgs $args
     */
    public function onDispatchLoopShutdown(\Enlight_Event_EventArgs $args)
    {
        foreach ( $this->setup->getCollectors() as $collector) {
            $collector->logResults($this->setup->getLogger());
        }
    }

    /**
     * @return bool
     */
    private function isDebugPluginActive()
    {
        return Shopware()->Plugins()->Core()->Debug()->Info()->get('active') === "1";
    }

    private function createClockworkLogDir()
    {
        $clockWorkLog = (new Container())->getClockworkLogPath();
        if (!is_dir($clockWorkLog)) {
            mkdir($clockWorkLog, 0755);
        }
    }

    /**
     * @return string
     */
    private function getVendorAutoloadFile() {
        return __DIR__ . '/vendor/autoload.php';
    }

    /**
     * @return bool
     */
    private function hasVendorAutoloadFile() {
        return file_exists($this->getVendorAutoloadFile());
    }


}
