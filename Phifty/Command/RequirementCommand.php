<?php
namespace Phifty\Command;
use CLIFramework\Command;

class RequirementCommand extends Command
{

    public function printResult($msg, $ok) 
    {
        $f = $this->formatter;
        echo sprintf("% -30s ",$msg);
        if( $ok ) {
            echo $f->format( "[  OK  ]" , 'success' );
        } else {
            echo $f->format( "[ FAIL ]" , 'fail' );
        }
        echo PHP_EOL;
    }

    public function execute() 
    {
        // xxx: Can use universal requirement checker.
        //
        // $req = new Universal\Requirement\Requirement;
        // $req->extensions( 'apc','mbstring' );
        // $req->classes( 'ClassName' , 'ClassName2' );
        // $req->functions( 'func1' , 'func2' , 'function3' )
        //
        $exts = array(
            'apc',
            'pdo',
            'mysqli',
        );
        foreach( $exts as $ext ) {
            $this->printResult($ext, extension_loaded($ext) );
        }

        $this->printResult('reflection', class_exists('ReflectionObject') );
        $this->printResult('lazyrecord', class_exists('LazyRecord\BaseModel',true));
        $this->printResult('assetkit',   class_exists('AssetToolkit\AssetLoader',true));
        $this->printResult('roller',     class_exists('Roller\Router',true));


        $this->printResult('roller extension', extension_loaded('roller') );

        $kernel = kernel();
        if( $configext = $kernel->config->get('Requirement.Extensions') ) {
            foreach( $configext as $extname ) {
                $this->printResult("$extname extension", extension_loaded($extname) );
            }
        }


        // TODO: 
        //   1. get services and get dependencies from these services for checking

    }
}


