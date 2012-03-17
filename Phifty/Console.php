<?php
/*
 * This file is part of the Phifty package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Phifty;
use CLIFramework\Application;

class Console extends Application
{

    function init()
    {
        parent::init();
        $this->registerCommand('export');
        $this->registerCommand('build-conf');
    }


    static function getInstance()
    {
        static $instance;
        if( $instance )
            return $instance;
        return $instance = new static;
    }
}


