<?php
namespace Phifty\Service;
use AssetToolkit;
use AssetToolkit\AssetConfig;
use AssetToolkit\AssetLoader;
use AssetToolkit\AssetCompiler;
use AssetToolkit\AssetRender;
use Exception;

class AssetService
    implements ServiceInterface
{

    function getId()
    {
        return 'asset';
    }

    /**
     *
     * $kernel->asset->loader
     * $kernel->asset->writer
     */
    function register($kernel, $options = array() ) 
    {
        $kernel->asset = function() use ($kernel) {
            $assetFile = PH_APP_ROOT . DIRECTORY_SEPARATOR . '.assetkit.php';
            if( ! file_exists($assetFile) ) {
                throw new Exception("$assetFile not found.");
            }

            $config = new AssetConfig( $assetFile , 
                $kernel->environment === 'production' 
                    ? array( 'cache' => true ) 
                    : array() 
            );

            $loader   = new AssetToolkit\AssetLoader($config);
            $compiler = new AssetToolkit\AssetCompiler($config,$loader);
            $render   = new AssetToolkit\AssetRender($config,$loader);

            if( $kernel->namespace ) {
                $compiler->setNamespace( $kernel->namespace );
            }

            return (object) array( 
                'loader' => $loader,
                'config' => $config,
                'render' => $render,
                'compiler' => $compiler,
            );
        };
    }
}

