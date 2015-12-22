<?php
namespace Shopware\Plugins\ShopwareClockwork\Clockwork\Components\Collector;

use Shopware\Components\Logger;
use Shopware\Plugin\Debug\Components\EventCollector as BaseEventCollector;

class EventCollector extends BaseEventCollector
{
    /**
     * @param Logger $log
     * @return mixed
     */
    public function logResults(Logger $log)
    {
        foreach (array_keys($this->results) as $event) {
            if (empty($this->results[$event][0])) {
                unset($this->results[$event]);
                continue;
            }
            $listeners = array();
            foreach (Enlight()->Events()->getListeners($event) as $listener) {
                $listener = $listener->getListener();
                if ($listener[0] === $this) {
                    continue;
                }
                if (is_array($listener) && is_object($listener[0])) {
                    $listener[0] = get_class($listener[0]);
                }
                if (is_array($listener)) {
                    $listener = implode('::', $listener);
                }
                $listeners[] = $listener;
            }
            $this->results[$event] = array(
                0 => $event,
                1 => $this->formatMemory(0 - $this->results[$event][1]),
                2 => 0 - $this->results[$event][2],
                3 => $listeners,
                4 => $this->results[$event][3]
            );
        }

        $this->results = array_values($this->results);

        foreach ($this->results as $result) {
            $order[] = $result[2];
        }
        array_multisort($order, SORT_NUMERIC, SORT_DESC, $this->results);

        array_unshift($this->results, array('name', 'memory', 'time', 'listeners', 'start'));

        $label = 'Benchmark Events';
        $table = array($label,
            $this->results
        );

        $log->table($table);
    }

    /**
     * @param \Enlight_Event_EventArgs $args
     * @return mixed
     */
    public function onBenchmarkEvent(\Enlight_Event_EventArgs $args)
    {
        $event = $args->getName();
        if (!isset($this->results[$event])) {
            $this->results[$event] = array(
                0 => true,
                1 => 0,
                2 => 0,
                3 => microtime(true)
            );
        }

        if (empty($this->results[$event][0])) {
            $this->results[$event][0] = true;
            $this->results[$event][1] -= memory_get_peak_usage(true);
            $this->results[$event][2] -= microtime(true);
        } else {
            $this->results[$event][0] = false;
            $this->results[$event][1] += memory_get_peak_usage(true);
            $this->results[$event][2] += microtime(true);
        }

        return $args->getReturn();
    }

    public function formatMemory($size)
    {
        if (empty($size)) {
            return '0.00 b';
        }
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @number_format($size / pow(1024, ($i = floor(log($size, 1024)))), 2, '.', '') . ' ' . $unit[$i];
    }

}
