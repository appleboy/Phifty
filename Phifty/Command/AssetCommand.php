<?php
namespace Phifty\Command;
use CLIFramework\Command;
use Phifty\Console;

/**
 * When running asset:init command, we should simply register app/plugin assets 
 * into .assetkit file.
 *
 * Then, By running asset:update command, phifty will install assets into webroot.
 *
 *      phifty.php asset init
 *
 *      phifty.php asset update
 */
class AssetCommand extends Command
{


    function brief() { return 'register and install assets'; }


    function options($opts)
    {
        $init = new AssetInstallCommand;
        $init->logger = $this->logger;
        $init->options($opts);
    }


    function init()
    {
        $this->registerCommand('init', 'Phifty\Command\AssetInitCommand');
        $this->registerCommand('install', 'Phifty\Command\AssetInstallCommand');
    }

    function execute() {
        $app = Console::getInstance();
        $init = new AssetInitCommand;
        $init->application = $app;
        $init->options = $this->options;
        $init->executeWrapper(array());

        $install = new AssetInstallCommand;
        $install->application = $app;
        $install->options = $this->options;
        $install->logger = $this->logger;
        $install->executeWrapper(array());
    }
}



