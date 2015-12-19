<?php
namespace Shopware\Plugins\ShopwareClockwork\Clockwork\Components;

use Monolog\Logger as BaseLogger;

/**
 * @category  Shopware
 * @package   Shopware\Components
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ClockworkLogger extends BaseLogger
{
    protected $data = [
        'viewsData' => []
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

        foreach ($data as $item) {
            if ($item[0] !== 'spec' && $item[1] !== 'value') {
                $this->data['viewsData'][] = ['data' => [
                    'name' => $item[0],
                    'data' => $item[1]
                ]];
            }
        }
    }


}
