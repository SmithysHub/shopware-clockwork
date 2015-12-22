<?php
namespace Shopware\Plugins\ShopwareClockwork\Clockwork\Components\Collector;

use Shopware\Components\Logger;
use Shopware\Plugin\Debug\Components\TemplateCollector as BaseTemplateCollector;

class TemplateCollector extends BaseTemplateCollector{
    /**
     * Logs all rendered templates into the internal log object.
     * Each logged template contains the template name, the required compile time,
     * the required render time and the required cache time.
     */
    public function logResults(Logger $log)
    {
        $rows = array(array('name', 'compile_time', 'render_time', 'cache_time', 'start_time'));
        foreach (\Smarty_Internal_Debug::$template_data as $template_file) {
            $template_file['name'] = str_replace($this->rootDir, '', $template_file['name']);
            $rows[] = array_values($template_file);
        }
        $label = "Benchmark Template";
        $table = array($label, $rows);

        $log->table($table);
    }

    public function formatTime($time)
    {
        return number_format($time, 5, '.', '');
    }

}