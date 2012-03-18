<?php
/*
 * This file is part of the Phifty package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Phifty\Environment;

class Production
{
    static function init($kernel)
    {
        // if we are in command-line mode, 
        /* for production mode */
        if( extension_loaded('xdebug') )
            xdebug_disable();
        error_reporting(0);

        set_exception_handler( function($e) use ($kernel) {
            $subject = 'ERROR: ' . $kernel->config->get('application','name') . ' - ' . $e->getMessage();
            // $to = 'cornelius.howl@gmail.com';
            // $content = '';
            // $content .= print_r( $e, true ) . "\n";
            // $content .= print_r( $_SERVER, true ) . "\n";
            // $content .= print_r( $_REQUEST, true ) . "\n";
            // $content .= print_r( $_SESSION, true ) . "\n";
            // xxx: show an error page of this
            mail( $to , $subject , $content );
        });
    }
}

