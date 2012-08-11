<?php
namespace Phifty\Testing;
use PHPUnit_Extensions_Selenium2TestCase;
use Exception;


abstract class Selenium2TestCase extends PHPUnit_Extensions_Selenium2TestCase 
{

    /**
     * @var array environment configuration for selenium testing
     */
    public $environment;
    
    protected function setUp()
    {
        $kernel = kernel();
        $kernel->config->load('testing','config/testing.yml');
        $config = $kernel->config->get('testing');
        if($config && $config->Selenium) {
            if($config->Selenium->Host)
                $this->setHost($config->Selenium->Host);

            if($config->Selenium->Port) 
                $this->setPort($config->Selenium->Port);

            if($config->Selenium->Browser)
                $this->setBrowser($config->Selenium->Browser);

            if($config->Environment)
                $this->environment = $config->Environment;

            $this->setBrowserUrl( $this->getBaseUrl() );
        }

        // XXX: SeleniumTestCase (1.0) seems don't support screenshotPath ?
        // $this->screenshotPath = $this->getScreenshotDir();
    }

    public function getBaseUrl() {
        $domain = kernel()->config->get('framework','Domain');
        return 'http://' . $domain;
    }

    // Override the original method and tell Selenium to take screen shot when test fails
    public function onNotSuccessfulTest(\Exception $e) 
    {
        $this->takeScreenshot('now.png');

        // use unix-timestamp so that we can sort file by name
        $this->takeScreenshot( str_replace('.','_',microtime(true)) . '.png' );
        return parent::onNotSuccessfulTest($e);
    }

    public function getScreenshotDir() {
        return PH_ROOT . '/tests/screenshots'; 
    }

    public function takeScreenshot($filename = null)
    {
        $image = $this->currentScreenshot();
        if ( !is_string( $image ) || ! $image ) {
            throw new Exception('screenshot failed, empty result.');
        }
        $path = $this->getScreenshotDir() . DIRECTORY_SEPARATOR . $filename;
        if( file_put_contents( $path , $image ) === false ) {
            throw new Exception("can not write screenshot image file $path");
        }
    }
}

