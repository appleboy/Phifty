<?php

namespace Phifty;
use Phifty\FileUtils;
use ReflectionClass;

/* a way to locate app model,controller,view ..etc class */
class AppClassKit
{
    static function detectPluginPath( $pluginName )
    {
        /* check if it's in app first */
        $appPluginDir = PH_APP_ROOT . DIR_SEP . 'plugins' . DIR_SEP . $pluginName;
        if( file_exists( $appPluginDir ) )
            return $appPluginDir;

        /* or in core ? */
        $corePluginDir = PH_ROOT . DIR_SEP . 'plugins' . DIR_SEP . $pluginName;
        if( file_exists( $corePluginDir ) )
            return $corePluginDir;

        return null;
    }

    static function pluginPaths()
    {
        $result = array();
        $list = webapp()->pluginList();

        foreach( $list as $name ) {
            $path = static::detectPluginPath( $name );
            if( $path )
                $result[] = $path;
        }
        return $result;
    }

    static function loadDir( $dir )
    {
        if( file_exists($dir ) ) {
            $files = FileUtils::expand_Dir( $dir );
            foreach( $files as $file ) {
                require_once $file;
            }
        }
    }

    /* return App Model classes */
    static function loadAppModels()
    {
        $dir = webapp()->getAppDir();
        $modelDir = $dir . DIRECTORY_SEPARATOR . 'Model';
        static::loadDir( $modelDir );
    }

    /* return core Model classes */
    static function loadCoreModels()
    {
        $dir = webapp()->getCoreDir();
        $modelDir = $dir . DIRECTORY_SEPARATOR . 'Model';
        static::loadDir( $modelDir );
    }

    static function loadPluginModels()
    {
        $paths = static::pluginPaths();
        foreach( $paths as $path ) {
            $modelPath = $path . DIR_SEP . 'Model';
            static::loadDir( $modelPath );
        }
    }

    /* get declared model classes */
    static function modelClasses()
    {
        $classes = get_declared_classes();
        $classes = array_filter( $classes , function($c) {
            $rf = new ReflectionClass($c);
            return is_a( $c, '\Lazy\Schema\SchemaDeclare' ) && ! $ref->isAbstract();
        });
        return $classes;
    }


}


?>
