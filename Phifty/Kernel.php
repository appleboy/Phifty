<?php
namespace Phifty;
require PH_ROOT . '/src/Phifty/ConfigLoader.php';
require PH_ROOT . '/src/Phifty/AppClassKit.php';
require PH_ROOT . '/src/Phifty/AppClassLoader.php';
require PH_ROOT . '/src/Phifty/CurrentUser.php';
require PH_ROOT . '/src/Phifty/L10N.php';
require PH_ROOT . '/src/Phifty/FileUtils.php';

use Phifty\Kernel;
use Phifty\CurrentUser;
use Phifty\L10N;
use Phifty\Web;
use Phifty\AppClassLoader;
use Phifty\AppClassKit;
use Phifty\FileUtils;
use Phifty\Action\ActionRunner;
use Universal\Container\ObjectContainer;
use Exception;


/*
    Phifty Main Controll Object:
        process startup operations

        init header
        init db
        init language
        init core object
        init plugin objects
        init app object

        run actions

        dispatch controllers
*/

class Kernel extends ObjectContainer 
{
    /* framework version */
    const VERSION = '2.2';

    /* rootDir: contains app, web, phifty dirs */
    public $rootDir; 

    /* phifty dir */
    public $frameworkDir;

    /* application namespace */
    public $appNs;

    /* boolean: is in command mode ? */
    public $isCLI;

    /* boolean: is in development mode ? */
    public $isDev = true;

    /**
     * application object pool
     *
     * app class name => app object
     */
    public $apps = array();

    public $environment = 'development';

    public function __construct( $environment = null ) 
    {
        /* define framework environment */
        $this->environment  = $environment ?: getenv('PHIFTY_ENV') ?: 'development';
        $this->frameworkDir = PH_ROOT; // Kernel is placed under framework directory
        $this->rootDir      = PH_APP_ROOT;
    }

    public function registerService( \Phifty\Service\ServiceInterface $service )
    {
        $service->register( $this );
    }

    public function init()
    {
        $this->event->trigger('phifty.before_init');
        $this->appName      = PH_APP_NAME;
        $this->isCLI        = isset($_SERVER['argc']);
        $self = $this;

        $this->currentUser = function() use ($self) {
            $currentUserClass = $self->config->get('application','current_user.class');
            return new $currentUserClass;
        };

        $this->web = function() use($self) { 
            return new \Phifty\Web( $self );
        };

        /**
         * detect for development mode 
         */
        $this->isDev = $this->environment == 'development';

        // Turn off all error reporting
        if( $this->isDev || $this->isCLI ) {
            \Phifty\Environment\Development::init($this);
        }
        else {
            \Phifty\Environment\Production::init($this);
        }


        /*
        $apploader = AppClassLoader::getInstance();
        $apploader->register();

        foreach( $this->config->apps as $class => $options ) {
            $app = new $class( $options );
        }

        $pluginConfigs = $this->config->get( 'plugins' );
        if( $pluginConfigs ) {
            foreach( $pluginConfigs as $name => $config ) {
                $loader->add( $name , array( PH_APP_ROOT . '/plugins' , PH_ROOT . '/plugins' ) );
            }
        }
         */

        define( 'CLI_MODE' , $this->isCLI );

        if( $this->isCLI ) {
            ini_set('output_buffering ', '0');
            ini_set('implicit_flush', '1');
            ob_implicit_flush(true);
        } else {
            ob_start();
            $s = $this->session; // build session object
            mb_internal_encoding('UTF-8');
        }

        $this->initPlugins();

        $this->event->trigger('phifty.after_init');

    }

    public function loadApp(MicroApp $class) 
    {
        $app = $class::getInstance();
        return $this->apps[ $class ] = $app;
    }

    public function getApp( $class )
    {
        if( isset($this->apps[ $class ]) )
            return $this->apps[ $class ];
    }


    public function isCLI()
    {
        return $this->isCLI;
    }



    /**
     * get current application name
     */
    public function getAppName()
    {
        return $this->appName;
    }


    /** 
     * application namespace
     */
    public function getAppNs()
    {
        return $this->appNs;
    }

    public function getAppPluginDir()
    {
        return $this->rootDir . DIR_SEP . 'plugins';
    }

    public function getFrameworkBundleDir()
    {
        return $this->frameworkDir . DIR_SEP . PHIFTY_APP_DIRNAME;
    }

    public function getCoreDir()
    {
        return $this->getFrameworkBundleDir() . DIR_SEP . 'Core';
    }


    /* we should move this into bundles dir */
    public function getFrameworkPluginDir()
    {
        return $this->frameworkDir . DIR_SEP . 'plugins';
    }

    public function getMinifiedWebDir()
    {
        return $this->rootDir . DIR_SEP . PHIFTY_WEBROOT_DIRNAME . DIR_SEP . 'static' . DIR_SEP . 'minified';
    }

    public function getAppWebDir()
    {
        return $this->rootDir  . DIR_SEP . PHIFTY_APP_DIRNAME . DIR_SEP . $this->appName . DIR_SEP . 'web';
    }

    public function getCoreWebDir()
    {
        return $this->getCoreDir() . DIR_SEP . 'web';
    }

    public function getWebRootDir()
    {
        return $this->rootDir . DIR_SEP . PHIFTY_WEBROOT_DIRNAME;
    }

    /**
     * Get exported plugin webdir
     * 
     * web dir structure
     *
     *   web/ph/plugins/sb/
     *   web/ph/plugins/product/
     *   web/ph/plugins/coupon/
     *   ..... etc
     * */
    public function getWebPluginDir()
    {
        return $this->rootDir . DIR_SEP . PHIFTY_WEBROOT_DIRNAME .  DIR_SEP . 'ph' . DIR_SEP . 'plugins';
    }


    /*
     * Get exported widget web dir
     *
     *     widgets/Foo/web => webroot/ph/widgets/Foo
     *
    */
    public function getWebAssetDir()
    {
        return $this->rootDir . DIR_SEP . PHIFTY_WEBROOT_DIRNAME . DIR_SEP . 'ph' . DIR_SEP . 'assets';
    }


    /**
     * Get Root Dir
     */
    public function getRootDir()
    {
        return $this->rootDir;
    }


    /**
     * return framework id
     */
    public function getFrameworkId()
    {
        return 'phifty';
    }

    public function getFrameworkDir()
    {
        return $this->frameworkDir;
    }



    /** 
     * Locale Related 
     */
    public function currentLocale()
    {
        return $this->locale->speaking();
    }

    public function currentLang()
    {
        return $this->currentLocale();
    }

    /* return Phifty\L10N */
    public function lang()
    {
        return $this->locale;
    }

    public function pluginList()
    {
    /*
        $config = (array) $this->config->get('application','plugins');
        if( ! $config )
            return array();
        return array_keys( $config );
     */
    }

    public function initPlugins()
    {
        /*
         * xxx:
        $pluginConfigs = $this->config->get( 'application', 'plugins' );
        if( ! $pluginConfigs )
            return;
        $this->plugin->loadFromList( $pluginConfigs );
         */
    }

    public function hasPlugin($name) 
    {
        return $this->plugin->hasPlugin( $name );
    }

    public function run() 
    {
        $this->event->trigger('phifty.before_run');

        // check if there is $_POST['action'] or $_GET['action']
        if( isset($_POST) || isset( $_GET ) || isset( $_FILES ) ) {

            // only run action in POST,GET method
            $runner = ActionRunner::getInstance();
            try 
            {
                $result = $runner->run();
                if( $result && $runner->isAjax() ) {
                    echo $result;
                    exit(0);
                }
            } 
            catch( Exception $e ) 
            {
                /**
                 * return 403 status forbidden
                 */
                header('HTTP/1.0 403');
                if( $runner->isAjax() ) {
                    die( json_encode( array( 'error' => $e->getMessage() ) ) );
                } else {
                    die( $e->getMessage() );
                }
            }
        }
        $this->event->trigger('phifty.after_run');
    }


    /**
     * backward-compatible
     */
    public function getPlugin($name) 
    {
        return $this->plugin->getPlugin( $name );
    }


    /**
     * get Phifty\Web object
     */
    public function web()
    {
        return $this->web;
    }

    /**
     * get Template Engine
     **/
    public function view()
    {
        return new \Phifty\View;
    }



	public function __toString()
	{
		return '<pre>' . get_class($this ) . '</pre>';
   	}


    static function getInstance()
    {
        static $one;
        if( $one )
            return $one;
        return $one = new static;
    }

}

