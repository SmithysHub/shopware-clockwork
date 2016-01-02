<?php

use Shopware\Plugins\ShopwareClockwork\Clockwork\Components\Log\ClockworkHandler;
use Shopware\Plugins\ShopwareClockwork\Subscriber\Container;

class Shopware_Controllers_Frontend_Clockwork extends \Enlight_Controller_Action
{

    public function preDispatch()
    {
        $this->Front()->Plugins()->ViewRenderer()->setNoRender();
    }

    public function postDispatch()
    {
        $data = $this->View()->getAssign();
        $data = Zend_Json::encode($data);
        
        $this->Response()->setHeader('Content-type', 'application/json', true);
        $this->Response()->setBody($data);
    }

    public function indexAction()
    {
        $id = $this->Request()->getParam('id', null);
        if( $id ) {
            $clockwork = $this->container->get('shopwareclockwork.clockwork');
            $this->View()->assign(json_decode($clockwork->getStorage()->retrieveAsJson($id), true));
            $this->getClockworkHandler()->deleteLog($id);
        }

    }

    /**
     * @return ClockworkHandler
     */
    protected function getClockworkHandler() {
        return new ClockworkHandler();
    }


}