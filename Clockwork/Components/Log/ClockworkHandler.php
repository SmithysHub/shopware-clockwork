<?php
namespace Shopware\Plugins\ShopwareClockwork\Clockwork\Components\Log;

use Enlight_Controller_Request_Request as Request;
use Monolog\Handler\AbstractProcessingHandler;
use Shopware\Plugins\ShopwareClockwork\Clockwork\DataSource\ShopwareDataSource;
use Shopware\Plugins\ShopwareClockwork\Subscriber\Container;

class ClockworkHandler extends AbstractProcessingHandler
{
    /**
     * @var \Shopware\Components\DependencyInjection\Container
     */
    protected $container;

    /**
     * ClockworkLogger constructor.
     */
    public function __construct()
    {
        $this->container = Shopware()->Container();
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function acceptsRequest(Request $request)
    {
        return (bool) preg_match('{\bChrome/\d+[\.\d+]*\b}', $request->getHeader('User-Agent'));
    }

    /**
     * @param array $record
     */
    protected function write(array $record)
    {
        $dataSource = $this->getShopwareDataSource();
        $message = $record['message'];
        $data = $record['context']['table'];
        array_shift($data);

        if (strpos($message, 'Template Vars') !== false) {
            $dataSource->formatViewData($message, $data);
        } elseif (strpos($message, 'Error Log') !== false) {
            $dataSource->formatErrorData($data);
        } elseif (strpos($message, 'Database Querys') !== false || strpos($message, 'Model Querys') !== false) {
            $dataSource->formatSqlQuerys($message, $data);
        } elseif (strpos($message, 'Benchmark Template') !== false) {
            $dataSource->setControllerTimeline($data);
        } elseif (strpos($message, 'Benchmark Events') !== false) {
            $dataSource->setEventInfo($data);
        } elseif (strpos($message, 'Benchmark Controller') !== false) {
            $dataSource->setControllerEventTimeline($data);
        }
    }

    /**
     * @return ShopwareDataSource
     */
    protected function getShopwareDataSource()
    {
        return $this->container->get('shopwareclockwork.datasource');
    }

    /**
     * @param int $id
     */
    public function deleteLog($id)
    {
        $logPath = $this->getPluginContainer()->getClockworkLogPath() . '/' . $id . '.json';
        if (is_file($logPath)) {
            unlink($logPath);
        }
    }

    /**
     * @return Container
     */
    protected function getPluginContainer()
    {
        return new Container;
    }
}
