<?php
namespace Phifty\Command;

use Phifty\FileUtils;
use Phifty\CodeTemplate;

class Init extends \Phifty\Command
{
    function run()
    {
        $kernel = \AppKernel::getInstance();

		$this->log( "Initializing phifty dirs..." );

		$this->log( "Webroot: " . $kernel->getRootDir() );

        $dirs = array();
        $dirs[] = FileUtils::path_join( $kernel->getRootDir() , 'cache' , 'view' );
        $dirs[] = FileUtils::path_join( $kernel->getRootDir() , 'cache' , 'config' );
		$dirs[] = $kernel->getWebRootDir();

		foreach( explode(' ','View web lib Model Action Controller template') as $subdir )
			$dirs[] = FileUtils::path_join( $kernel->getAppDir() , $subdir );

        $dirs[] = $kernel->getWebRootDir() . DIRECTORY_SEPARATOR . 'ph' . DIRECTORY_SEPARATOR . 'plugins';

		/* hard links */
        $dirs[] = $kernel->getWebRootDir() . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'images';
        $dirs[] = $kernel->getWebRootDir() . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'css';
        $dirs[] = $kernel->getWebRootDir() . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'js';
        $dirs[] = $kernel->getWebRootDir() . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'upload';

        FileUtils::mkpath($dirs,true);

        $codegen = new CodeTemplate;

        $appFile = FileUtils::path_join( $kernel->getAppDir() , 'Application.php' );
        $codegen->renderFile( $appFile , 'app.php' , array( "AppName" => $kernel->getAppName()));

		$htaccessFile = $kernel->getWebRootDir() . DIRECTORY_SEPARATOR . '.htaccess';
        $codegen->renderFile( $htaccessFile, 'htaccess' );

        $webrootIndex = $kernel->getWebRootDir() . DIRECTORY_SEPARATOR . 'index.php';
        $codegen->renderFile( $webrootIndex , 'webroot_index.php' );

		$this->log( "Changing permissions..." );
        $chmods = array();
        $chmods[] = array( "ga+rw" , "cache" );
        $chmods[] = array( "ga+rw" , $kernel->getCoreWebDir() );
        $chmods[] = array( "ga+rw" , $kernel->getWebRootDir() . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'upload' );
        foreach( $chmods as $mod ) {
			$this->log( "\t{$mod[0]} {$mod[1]}" );
            system("chmod -R {$mod[0]} {$mod[1]}");
        }

    }
}

