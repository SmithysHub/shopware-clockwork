<?php

class Shopware_Controllers_Frontend_Clockwork extends Enlight_Controller_Action
{
    public function indexAction()
    {
        $id = $this->Request()->getParam('id', null);
    }

}