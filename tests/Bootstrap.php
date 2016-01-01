<?php

require "./../../../../../../tests/Shopware/TestHelper.php";

$pluginDir = __DIR__ . '/../';

require_once $pluginDir . '/vendor/autoload.php';

\TestHelper::Instance()->Loader()->registerNamespace(
    'Shopware\\Plugins\\' . basename(dirname(__DIR__)),
    $pluginDir
);

Shopware()->Front()->setRequest(new Enlight_Controller_Request_RequestHttp());
