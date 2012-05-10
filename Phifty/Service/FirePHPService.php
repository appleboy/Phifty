<?php
namespace Phifty\Service;

class FirePHPService 
    implements ServiceInterface
{

    public function getId() { return 'FirePHP'; }

    public function register($kernel, $options = array() )
    {
        // skip this plugin if we are not in development mode
        // or if we are in command-line mode.
        if( $kernel->environment !== 'development' || $kernel->isCLI ) {
            return;
        }

        /**
         * init firephp for development:
         * http://www.firephp.org/HQ/Use.htm
         *
         * $ pear channel-discover pear.firephp.org
         * $ pear install firephp/FirePHPCore 
         **/
        require $kernel->frameworkDir . '/vendor/firephp/lib/FirePHPCore/fb.php';

        // Object-oriented methods
        // require $kernel->frameworkDir . '/vendor/firephp/lib/FirePHPCore/FirePHP.class.php';


        // if firebug supports
        $kernel->event->register('phifty.after_run', function() use ($kernel) {
            if( function_exists('fb') ) {
                fb( (memory_get_usage() / 1024 / 1024 ) . ' MB'  , 'Memory Usage' );
                fb( (memory_get_peak_usage() / 1024 / 1024 ) . ' MB'  , 'Memory Peak Usage' );
                fb( (time() - $_SERVER['REQUEST_TIME']) , 'Request time' );
            }
        });
    }
}
