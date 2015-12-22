<?php
namespace Shopware\Plugins\ShopwareClockwork\Clockwork\Components\Collector;

use Shopware\Plugin\Debug\Components\ControllerCollector as BaseControllerCollector;

class ControllerCollector extends BaseControllerCollector
{
    /**
     * Logs all controller events into the internal log object.
     * Each logged events contains the event name, the execution time and the allocated peak of memory.
     *
     * @param \Enlight_Event_EventArgs $args
     * @return void
     */
    public function onBenchmarkEvent(\Enlight_Event_EventArgs $args)
    {
        if (empty($this->results)) {
            $this->results[] = array('name', 'memory', 'time', 'starttime');
            $this->startTime = microtime(true);
            $this->startMemory = memory_get_peak_usage(true);
        }

        $this->results[] = array(
            0 => str_replace('Enlight_Controller_', '', $args->getName()),
            1 => $this->utils->formatMemory(memory_get_peak_usage(true) - $this->startMemory),
            2 => microtime(true) - $this->startTime,
            3 => $this->startTime
        );
    }
}
