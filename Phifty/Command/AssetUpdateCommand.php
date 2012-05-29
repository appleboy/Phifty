<?php
namespace Phifty\Command;
use CLIFramework\Command;

/**
 * When running asset:init command, we should simply register app/plugin assets 
 * into .assetkit file.
 *
 * Then, By running asset:update command, phifty will install assets into webroot.
 */
class AssetUpdateCommand extends Command
{
    function options($opts)
    {
        $opts->add('l|link','use symbolic link');
    }

    function registerAsset($config,$installer,$dir)
    {
        $manifestPath = $dir  . DIRECTORY_SEPARATOR . 'manifest.yml';
        if( ! file_exists($manifestPath)) 
            throw new Exception( "$manifestPath does not exist." );

        $asset = new \AssetKit\Asset($manifestPath);
        $asset->config = $config;
        $installer->install( $asset );

        $export = $asset->export();
        $config->addAsset( $asset->name , $export );

        $this->logger->info("Saving config...");
        $config->save();
    }


    function execute() 
    {
        $config = new \AssetKit\Config('.assetkit');
        $kernel = kernel();

        if( $this->options->link ) {
            $installer = new \AssetKit\LinkInstaller;
        } else {
            $installer = new \AssetKit\Installer;
        }

        $this->logger->info("Finding assets from applications...");
        foreach( $kernel->applications as $application ) {
            foreach( $application->getAssetDirs() as $dir ) {
                $this->registerAsset($config,$installer,$dir);
            }
        }

        $this->logger->info("Finding assets from plugins...");
        foreach( $kernel->plugins as $plugin ) {
            foreach( $plugin->getAssetDirs() as $dir ) {
                $this->registerAsset($config,$installer,$dir);
            }
        }
    }
}

