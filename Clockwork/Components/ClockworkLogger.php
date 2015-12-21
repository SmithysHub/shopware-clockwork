<?php
namespace Shopware\Plugins\ShopwareClockwork\Clockwork\Components;

use Clockwork\Clockwork;
use Shopware\Components\Logger;
use Psr\Log\LogLevel;

/**
 * @category  Shopware
 * @package   Shopware\Components
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ClockworkLogger extends Logger
{
    protected $data = [
        'viewsData' => [],
        'log' => [],
        'databaseQueries' => [],
        ''
    ];
    /**
     * @param string|array $label
     * @param null|array $data
     */
    public function table($label, $data = null)
    {
        if (is_array($label)) {
            list($label, $data) = $label;
        }

        if( strpos($label, 'Template Vars') !== false ) {
            $this->formatViewData($label, $data);
        } elseif  ( strpos($label, 'Error Log') !== false ) {
            $this->formatErrorData($data);
        } elseif  ( strpos($label, 'Database Querys') !== false || strpos($label, 'Model Querys') !== false) {
            $this->formatSqlQuerys($label, $data);
        }
    }

    public function setBaseInfo( \Enlight_Controller_Request_RequestHttp $request ) {
        $this->data['controller'] = $request->getControllerName();
        $this->data['getData'] = $request->getQuery();
        $this->data['postData'] = $request->getPost();
    }

    /**
     * @param string $label
     */
    public function trace($label)
    {
        // @toDo wait for implementation
        die(PHP_EOL . '<br>die: ' . __FUNCTION__ .' / '. __FILE__ .' / '. __LINE__);
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $label
     * @param array $data
     */
    protected function formatViewData($label, array $data)
    {
        $this->data['viewsData'][] = ['data' => [
            'name' => $label,
            'data' => ''
        ]];

        foreach ($data as $key => $item) {
            if ($key !== 0) {
                $this->data['viewsData'][] = ['data' => [
                    'name' => $item[0],
                    'data' => $item[1]
                ]];
            }
        }
    }

    /**
     * @param array $data
     */
    protected function formatErrorData(array $data)
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

    protected function formatSqlQuerys($label, array $data)
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

    /**
     * @return Clockwork
     */
    protected function getClockWork(){
        return Shopware()->Container()->get('shopwareclockwork.clockwork');
    }

}
