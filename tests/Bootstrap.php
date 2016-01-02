<?php

require "./../../../../../../tests/Shopware/TestHelper.php";

$pluginDir = __DIR__ . '/../';
$pluginName = basename(dirname(__DIR__));
$pluginFolder = basename(dirname(dirname(__DIR__)));

require_once $pluginDir . '/vendor/autoload.php';


\TestHelper::Instance()->Loader()->registerNamespace(
    'Shopware\\Plugins\\' . $pluginName,
    $pluginDir
);

\TestHelper::Instance()->Loader()->registerNamespace(
    'Shopware\\Tests\\' . $pluginName,
    __DIR__ . '/'
);

Shopware()->Plugins()->$pluginFolder()->$pluginName()->onStartDispatch();

Shopware()->Front()->setRequest(new Enlight_Controller_Request_RequestHttp());
