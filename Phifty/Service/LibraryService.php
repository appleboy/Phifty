<?php
namespace Phifty\Service;
use Exception;

class LibraryLoader {

    public $kernel;

    public $throwOnFail = true;

    public $paths = array();

    public $loaded = array();

    function __construct($kernel)
    {
        $this->kernel = $kernel;
        $this->paths[] = PH_APP_ROOT . DS . 'libraries';
        $this->paths[] = PH_ROOT . DS . 'libraries';
    }

    function getPaths()
    {
        return $this->paths;
    }

    function addPath($path) 
    {
        $this->paths[] = $path;
    }

    function insertPath($path) 
    {
        $this->paths = array_unshift( $this->paths , $path );
    }

    function load( $name ) {
        if( isset( $this->loaded[ $name ] ) ) {
            return $this->loaded[ $name ];
        }

        foreach( $this->getPaths() as $path ) {
            $dir = $path . DS . $name;
            $initFile = $dir . DS . 'init.php';
            if( file_exists($dir) && is_dir($dir) ) {
                if( ! file_exists( $initFile ) ) {
                    throw new Exception("$initFile not found.");
                }
                require $initFile;
                return $dir;
            }
        }

        if( $this->throwOnFail ) {
            throw new Exception("Can not load library $name");
        }
        return false;
    }
}


/***
 * if( $dir = kernel()->library->load('google-recaptcha') ) {
 *
 * }
 */
class LibraryService
    implements ServiceInterface
{
    public $classloader;

    public function getId() { return 'LibraryLoader'; }

    public function register($kernel,$options = array())
    {
        $self = $this;
        $kernel->library = function() use($self,$kernel) {
            return new LibraryLoader($kernel);
        };
    }

}

