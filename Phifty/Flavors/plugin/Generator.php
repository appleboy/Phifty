<?php
namespace plugin;
use GenPHP\Flavor\BaseGenerator;

class Generator extends BaseGenerator
{
    function brief() { return 'generate plugin structure'; }

    function generate($pluginName) 
    {
        $pluginDir = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $pluginName;
        $this->createDir( $pluginDir );
        $this->createDir( $pluginDir . DIRECTORY_SEPARATOR . 'Model' );
        $this->createDir( $pluginDir . DIRECTORY_SEPARATOR . 'Controller' );
        $this->createDir( $pluginDir . DIRECTORY_SEPARATOR . 'Action' );
        $this->createDir( $pluginDir . DIRECTORY_SEPARATOR . 'template' );
        $this->createDir( $pluginDir . DIRECTORY_SEPARATOR . 'assets' );
    }
}
