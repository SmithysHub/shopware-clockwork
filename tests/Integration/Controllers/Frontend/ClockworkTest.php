<?php

namespace Shopware\Tests\ShopwareClockwork\Integration\Controllers\Frontend;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class ClockworkTest extends \Enlight_Components_Test_Controller_TestCase
{
    protected $logName = 'clockwork-unit';


    public function testIndexActionWithContent()
    {
        $content = [
            'id' => 'unittest',
            'time' => 123
        ];
        file_put_contents($this->getLogFilePath(), json_encode($content));

        $this->Request()
            ->setMethod('GET');
        $this->dispatch('/Clockwork/index/id/' . $this->logName);

        $defaultBody = json_decode($this->Response()->getBody('default'), true);

        $this->assertTrue($this->Response()->getHeaders()[0]["value"] === 'application/json' );
        $this->assertTrue($defaultBody['id'] === $content['id'] );
        $this->assertTrue($defaultBody['time'] === $content['time'] );

        $this->reset();
    }


    public function testIndexActionWithOutContent()
    {
        $this->Request()
            ->setMethod('GET');
        $this->dispatch('/Clockwork/index/id/nofile');

        $defaultBody = json_decode($this->Response()->getBody('default'), true);

        $this->assertTrue($this->Response()->getHeaders()[0]["value"] === 'application/json' );
        $this->assertTrue($this->Response()->getHeaders()[0]["value"] === 'application/json' );
        $this->assertTrue(empty($defaultBody));
        $this->assertTrue(is_array($defaultBody));

        $this->reset();
    }



    /**
     * Performs operation returned by getSetUpOperation().
     */
    protected function tearDown()
    {
        parent::tearDown();

        $filePath = $this->getLogFilePath();
        if( is_file($filePath) ) {
            unlink($filePath);
        }
    }

    /**
     * @return string
     */
    protected function getUnitClockWorkLogDir() {
        $clockWorkLog = Shopware()->Container()->get('kernel')->getLogDir() . DIRECTORY_SEPARATOR . 'clockwork' . DIRECTORY_SEPARATOR;
        if (!is_dir($clockWorkLog)) {
            mkdir($clockWorkLog, 0755);
        }
        return $clockWorkLog;
    }

    /**
     * @return string
     */
    protected function getLogFilePath() {
        return $this->getUnitClockWorkLogDir() . $this->logName . '.json';
    }

}