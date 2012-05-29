<?php
namespace Phifty\Command;
use CLIFramework\Command;

/**
 * When running asset:init command, we should simply register app/plugin assets 
 * into .assetkit file.
 *
 * Then, By running asset:update command, phifty will install assets into webroot.
 */
class AssetInitCommand extends Command
{
    function registerAsset($config,$dir)
    {
        $manifestPath = $dir  . DIRECTORY_SEPARATOR . 'manifest.yml';
        if( ! file_exists($manifestPath)) 
            throw new Exception( "$manifestPath does not exist." );

        $asset = new \AssetKit\Asset($manifestPath);
        $asset->config = $config;
        $asset->initResource(true); // update it

        // export config to assetkit file
        $config->addAsset( $asset->name , $asset->export() );

        $this->logger->info("{$asset->name} added.", 1);
        $config->save();
    }

    function execute() 
    {
        $config = new \AssetKit\Config('.assetkit');
        $kernel = kernel();

        $this->logger->info("Finding assets from applications...");
        foreach( $kernel->applications as $application ) {
            $this->logger->info( ' - ' . get_class($application) );
            foreach( $application->getAssetDirs() as $dir ) {
                $this->registerAsset($config,$dir);
            }
        }

        $this->logger->info("Finding assets from plugins...");
        foreach( $kernel->plugins as $plugin ) {
            $this->logger->info( ' - ' . get_class($plugin) );
            foreach( $plugin->getAssetDirs() as $dir ) {
                $this->registerAsset($config,$dir);
            }
        }
    }
}

