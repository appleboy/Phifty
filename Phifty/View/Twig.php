<?php
namespace Phifty\View;

use Phifty\View\Engine;
use Phifty\FileUtils;
use Phifty\ClassUtils;

use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_Function_Function;

use Twig_Extension_Debug;
use Twig_Extension_Optimizer;
use Twig_Extension_Escaper;
use Twig_Loader_String;

use Twig_Extensions_Extension_Text;
use Twig_Extensions_Extension_I18n;

class Twig extends \Phifty\View\Engine 
//    implements \Phifty\View\EngineInterface
{
    public $loader;
    public $env;

    public function newRenderer()
    {
        $cacheDir = $this->getCachePath();
        $dirs     = $this->getTemplateDirs();
        $loader   = new Twig_Loader_Filesystem( $dirs );

        $envOpts = array();

        $kernel = kernel();
        $isDev = $kernel->isDev;

        // get twig config
        if( $isDev ) {
            $envOpts['cache'] = $cacheDir;
            $envOpts['debug'] = true;
            $envOpts['auto_reload'] = true;
        }
        else {
            $envOpts['cache'] = $cacheDir;
        }

        /* if twig config is defined, then override the current config */
        $twigConfig = $kernel->config->get( 'View.Twig' );
        if( $twigConfig && is_array( $twigConfig ) ) {
            $envOpts = array_merge( $envOpts , $twigConfig );
        }

        /* 
         * Env Options
         * http://www.twig-project.org/doc/api.html#environment-options
         * */
        $this->env = new Twig_Environment($loader, $envOpts );

        /* load extensions from config settings */
        if( $twigConfig ) {

            if( isset($twigConfig['CoreExtensions'] ) ) {
                foreach( $twigConfig['CoreExtensions'] as $extension ) {
                    $extname = null;
                    $options = null;
                    if( is_string($extension) ) {
                        $extname = $extension;
                    } elseif ( is_array( $extension ) ) {
                        $extname = key($extension);
                        $options = $extension[ $extname ];
                    }
                    $class = 'Twig_Extension_' . $extname;
                    $this->env->addExtension( ClassUtils::new_class( $class , $options ) );
                }
            }

            if( isset($twigConfig['Extensions'] ) ) { 
                foreach( $twigConfig['Extensions'] as $extension ) {
                    $extname = null;
                    $options = null;
                    if( is_string($extension) ) {
                        $extname = $extension;
                    } elseif ( is_array( $extension ) ) {
                        $extname = key($extension);
                        $options = $extension[ $extname ];
                    }
                    $class = 'Twig_Extensions_Extension_' . $extname;
                    $this->env->addExtension( ClassUtils::new_class( $class , $options ) );
                }
            }

        } else {
            /* Default extensions */

            /* if twig config is not define, then use our dev default extensions */
            if( $isDev ) {
                $this->env->addExtension( new Twig_Extension_Debug );
            } else {
                // for production mode
                $this->env->addExtension( new Twig_Extension_Optimizer );
            }

            $this->env->addExtension( new Twig_Extensions_Extension_Text );
            $this->env->addExtension( new Twig_Extensions_Extension_I18n );
        }
        $this->loader = $loader;
        $this->registerFunctions();
        return $this->env;
    }

    function registerFunctions()
    {
        $this->env->addFunction('uniqid'  , new Twig_Function_Function('uniqid'));
        $this->env->addFunction('md5'     , new Twig_Function_Function('md5'));
        $this->env->addFunction('time'    , new Twig_Function_Function('time'));
        $this->env->addFunction('sha1'    , new Twig_Function_Function('sha1'));
        $this->env->addFunction('gettext' , new Twig_Function_Function('gettext'));
        $this->env->addFunction('_'       , new Twig_Function_Function('_'));
    }

    function newStringRenderer()
    {
        return new Twig_Environment( new Twig_Loader_String );
    }

    function render( $template,$args = array() )
    {
        return $this->getRenderer()->loadTemplate( $template )->render( $args );
    }

    function display( $template , $args = array() )
    {
        $this->getRenderer()->loadTemplate( $template )->display($args);
    }

    function renderString( $stringTemplate , $args = array() )
    {
        $twig = $this->newStringRenderer();
        return $twig->loadTemplate( $stringTemplate )->render( $args );
    }

    function displayString( $stringTemplate , $args = array() )
    {
        $twig = $this->newStringRenderer();
        $twig->loadTemplate( $stringTemplate )->display($args);
    }

}
