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
    const name = 'phifty';

    function getVersion() {
        return Kernel::VERSION;
    }

    function init()
    {
        parent::init();
        $this->registerCommand('init');
        $this->registerCommand('create');
        $this->registerCommand('build-conf');
        $this->registerCommand('export');
        $this->registerCommand('locale');
        $this->registerCommand('console');
        $this->registerCommand('router');
        $this->registerCommand('asset');
        $this->registerCommand('new','Phifty\Command\GenerateCommand');
        $this->registerCommand('requirement');

        $this->registerCommand('build-schema','LazyRecord\Command\BuildSchemaCommand');
        $this->registerCommand('build-sql','LazyRecord\Command\BuildSqlCommand');
    }

    static function getInstance()
    {
        static $instance;
        if( $instance )
            return $instance;
        return $instance = new static;
    }
}


