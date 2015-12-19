<?php
namespace Shopware\Plugins\ShopwareClockwork\Clockwork\Components;

use Shopware\Components\Logger;

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
        }
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
    public function formatViewData($label, array $data)
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
     * @param $label
     * @param array $data
     */
    public function formatErrorData(array $data)
    {
        array_shift($data);
        foreach ($data as $item) {
            $level = 3;
            if ( $item[2] === 'E_WARNING' ) {
                $level = 4;
            } elseif( $item[2] === 'E_RECOVERABLE_ERROR' ){
                $level = 5;
            }
            $this->data['log'][] =
                array(
                    'time' => 0,
                    'level' => $level,
                    'message' => $item[2] . ' | ' . $item[3] . ' in: ' . $item[5] . ':' . $item[4]
                );
        }
    }


}
