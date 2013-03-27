<?php
namespace Phifty;
use Phifty\Kernel;
use Phifty\Locale;
use Phifty\Web;
use Universal\Container\ObjectContainer;
use Phifty\Service\ServiceInterface;

class Kernel extends ObjectContainer
{
    /* framework version */
    const FRAMEWORK_ID = 'phifty';
    const VERSION = '2.6.0';

    public $applicationID;
    public $applicationName;
    public $applicationUUID;

    public $frameworkDir;
    public $frameworkAppDir;
    public $frameworkBundleDir;

    public $cacheDir;

    public $rootDir;  // application root dir
    public $rootAppDir;   // application dir (./applications)
    public $rootBundleDir;
    public $webroot;

    /* boolean: is in command mode ? */
    public $isCLI;

    /* boolean: is in development mode ? */
    public $isDev = true;

    /**
     * application object pool
     *
     * app class name => app object
     */
    public $applications = array();

    public $environment = 'development';

    public $services = array();

    public function __construct( $environment = null )
    {
        /* define framework environment */
        $this->environment  = $environment ?: getenv('PHIFTY_ENV') ?: 'development';
        $this->isCLI        = isset($_SERVER['argc']) && !isset($_SERVER['HTTP_HOST']);

        // detect development mode
        $this->isDev = $this->environment === 'development';

        // build path info
        $this->frameworkDir       = PH_ROOT;
        $this->frameworkAppDir    = PH_ROOT . DS . 'applications';
        $this->frameworkBundleDir = PH_ROOT . DS . 'bundles';
        $this->rootDir            = PH_APP_ROOT;      // Application root.
        $this->rootAppDir         = PH_APP_ROOT . DS . 'applications';
        $this->rootBundleDir      = PH_APP_ROOT . DS . 'bundles';
        $this->webroot            = PH_APP_ROOT . DS . 'webroot';
        $this->cacheDir           = PH_APP_ROOT . DS . 'cache';

        defined('CLI_MODE')
            || define( 'CLI_MODE' , $this->isCLI );
        mb_internal_encoding('UTF-8');
        if (! $this->isCLI) {
            ob_start();
        }
    }

    public function getVersion()
    {
        return self::VERSION;
    }

    public function getCacheDir()
    {
        return $this->cacheDir;
    }



    /**
     * Get application UUID from config
     *
     * @return string Application UUID
     */
    public function getApplicationUUID()
    {
        if ( $this->applicationUUID )
            return $this->ApplicationUUID;
        return $this->ApplicationUUID = $this->config->framework->ApplicationUUID;
    }

    public function getApplicationID()
    {
        if ( $this->applicationID ) 
            return $this->applicationID;
        return $this->applicationID = $this->config->framework->ApplicationID;
    }

    /**
     * Get current application name from config
     *
     * @return string Application name
     */
    public function getApplicationName()
    {
        if ( $this->applicationName )
            return $this->applicationName;
        return $this->applicationName = $this->config->framework->ApplicationName;
    }


    public function registerService( ServiceInterface $service, $options = array() )
    {
        $service->register( $this , $options );
        $this->services[ $service->getId() ] = $service;
    }

    /**
     * Run initialize after services were registered.
     */
    public function init()
    {
        $this->event->trigger('phifty.before_init');
        $self = $this;

        $this->web = function() use ($self) {
            return new \Phifty\Web( $self );
        };

        // Turn off all error reporting
        if ($this->isDev || $this->isCLI) {
            \Phifty\Environment\Development::init($this);
        } else {
            \Phifty\Environment\Production::init($this);
        }

        if ($this->isCLI) {
            \Phifty\Environment\CommandLine::init($this);
        }

        if ( isset($this->session) ) {
            $this->session;
        }
        if ( isset($this->locale) ) {
            $this->locale;
        }

        if ( $appconfigs = $this->config->get('framework','Applications') ) {
            foreach ($appconfigs as $appname => $appconfig) {
                $this->loadApp( $appname , $appconfig );
            }
        }

        $this->event->trigger('phifty.after_init');
    }

    /**
     * Create application object
     */
    public function loadApp($appname, $config = array() )
    {
        $class = $appname . '\Application';
        $app = $class::getInstance();
        $app->config = $config;
        $app->init();

        return $this->applications[ $appname ] = $app;
    }

    /**
     * Get application object
     *
     * @param string application name
     *
     * @code
     *
     *   kernel()->app('Core')->getController('ControllerClass');
     *   kernel()->app('Core')->getModel('ModelClass');
     *   kernel()->app('Core')->getNamespace();
     *   kernel()->app('Core')->locate();
     *
     * @endcode
     */
    public function app( $appname )
    {
        if ( isset($this->applications[ $appname ]) ) {
            return $this->applications[ $appname ];
        }
    }

    /**
     * Get service object by its identifier
     *
     * @param  string  $id
     * @return Service object
     */
    public function service($id)
    {
        if ( isset($this->services[ $id ] ) ) {
            return $this->services[ $id ];
        }
    }

    /**
     * Get plugin object from plugin service
     *
     * backward-compatible
     *
     * @param string $name
     */
    public function plugin($name)
    {
        return $this->plugins->get( $name );
    }



    /**
     * Since we are migrating plugin to bundle.
     *
     * @param string $name
     */
    public function bundle($name, $lookup = false)
    {
        return $this->plugins->get($name);
    }



    /**
     * return framework id
     */
    public function getFrameworkId()
    {
        return self::FRAMEWORK_ID;
    }

    public static function getInstance()
    {
        static $one;
        if ( $one )

            return $one;
        return $one = new static;
    }
}
