<?php
namespace Shopware\Plugins\ShopwareClockwork\Clockwork\DataSource;

use Clockwork\DataSource\DataSource;
use Clockwork\Request\Request;
use Clockwork\Clockwork;
use Psr\Log\LogLevel;

class ShopwareDataSource extends DataSource
{
    /**
     * @var \Shopware\Components\DependencyInjection\Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $data = [
        'viewsData' => [],
        'log' => [],
        'databaseQueries' => [],
        ''
    ];

    /**
     * ClockworkLogger constructor.
     */
    public function __construct()
    {
        $this->container = Shopware()->Container();
    }

    public function setBaseInfo( \Enlight_Controller_Request_RequestHttp $request ) {
        $this->data['controller'] = $request->getControllerName();
        $this->data['getData'] = $request->getQuery();
        $this->data['postData'] = $request->getPost();
    }

    /**
     * @param $label
     * @param array $data
     */
    public function formatViewData($label, array $data)
    {
        $this->data['viewsData'][] = ['data' => [
            'name' => $label,
            'data' => ''
        ]];

        array_shift($data);
        foreach ($data as $item) {
            $this->data['viewsData'][] = ['data' => [
                'name' => $item[0],
                'data' => $item[1]
            ]];
        }
    }

    /**
     * @param array $data
     */
    public function formatErrorData(array $data)
    {
        array_shift($data);
        foreach ($data as $item) {
            $level = LogLevel::INFO;
            $message = $item[2] . ' | ' . $item[3] . ' in: ' . $item[5] . ':' . $item[4];
            if ( $item[2] === 'E_WARNING' ) {
                $level = LogLevel::WARNING;
            } elseif( $item[2] === 'E_RECOVERABLE_ERROR' ){
                $level = LogLevel::ERROR;
            } elseif( $item[2] === 'E_NOTICE' ){
                $level = LogLevel::NOTICE;
            }
            $this->getClockWork()->log($level, $message);
        }
    }

    public function formatSqlQuerys($label, array $data)
    {
        array_shift($data);
        $this->data['databaseQueries'][] = [
            'query' => $label,
            'duration' => 0
        ];

        foreach ($data as $item) {
            $query = $item[2];
            if( !empty($item[3]) ) {
                $query .= ' | Params: ' . json_encode($item[3]);
            }
            if( $item[1] > 1 ) {
                $query .= ' | Count: ' . $item[1];
            }
            $this->data['databaseQueries'][] = [
                'query' => $query,
                'duration' => (float)$item[0]
            ];
        }
    }

    public function setControllerEventTimeline(array $data){
        $clockWork = $this->getClockWork();

        array_shift($data);
        foreach ($data as $item) {
            $clockWork->getTimeline()->addEvent($item[0], $item[0], $item[3], $item[2] + $item[3]);
        }

    }


    public function setControllerTimeline(array $data){
        $clockWork = $this->getClockWork();

        array_shift($data);
        foreach ($data as $item) {
            $clockWork->getTimeline()->addEvent($item[0], $item[0], $item[4], $item[4] + $item[2] + $item[3]);
        }

    }

    public function setEventInfo(array $data){
        $clockWork = $this->getClockWork();

        array_shift($data);
        foreach ($data as $item) {
            $clockWork->getTimeline()->addEvent($item[0], $item[0], $item[4], $item[4] + $item[2]);
            $clockWork->log($item[0], $item[3]);
        }
    }

    /**
     * the entry-point. called by Clockwork itself.
     *
     * @param Request $request
     * @return Request
     */
    public function resolve(Request $request)
    {
        $data = $this->getData();
        foreach ($data as $name => $item) {
            if($name === 'postData') {
                $item = $this->removePasswords($item);
            }
            $request->{$name} =  $item;
        }

        return $request;
    }

    /**
     * @return array
     */
    protected function getData()
    {
        return $this->data;
    }

    /**
     * @return Clockwork
     */
    protected function getClockWork(){
        return $this->container->get('shopwareclockwork.clockwork');
    }

}