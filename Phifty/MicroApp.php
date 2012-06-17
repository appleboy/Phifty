<?php
namespace Phifty;
use ActionKit\ActionRunner;
use ReflectionClass;
use ReflectionObject;

/**
 *  MicroApp is the base class of App, Core, {Plugin} class.
 */
class MicroApp extends \Phifty\Singleton
{
    public $config;

    function init() { }

    function getId()
    {
        return $this->getNamespace();
    }


    /**
     * get the namespace name,
     *
     * for \Product\Application, we get Product.
     *
     * */
    public function getNamespace()
    {
        $object = new ReflectionObject($this);
        return $object->getNamespaceName();
    }

    /**
     * helper method, route path to template
     *
     * @param string $path
     * @param string $template file
     */
    function page( $path , $template , $args = array() )
    {
        $this->add( $path , array( 
            'template' => $template,
            'args' => $args,  // template args
        ));
    }

    /**
     * Locate plugin app dir path.
     */
    function locate()
    {
        $object = new ReflectionObject($this);
        return dirname($object->getFilename());
    }


    /**
     * get the model in the namespace of current microapp 
     */
    public function getModel( $name )
    {
        $class = sprintf('%s\Model\%s',$this->getNamespace(),$name);
        return new $class;
    }

    public function getController( $name )
    {
        $class = sprintf('%s\Controller\%s',$this->getNamespace(),$name);
        return new $class;
    }

    public function getAction( $name )
    {
        $class = sprintf('%s\Action\%s',$this->getNamespace(),$name);
        return new $class;
    }

    public function getConfig()
    {
        return $this->config;
    }


    /**
     * XXX: make this simpler......orz
     *
     *
     * In route method, we can do route with:
     *
     * $this->route('/path/to', array( 
     *          'controller' => 'ControllerClass'
     *  ))
     * $this->route('/path/to', 'ControllerClass' );
     *
     * Mapping to actionNameAction method.
     *
     * $this->route('/path/to', 'ControllerClass:actionName' )  
     *
     * $this->route('/path/to', array( 
     *          'template' => 'template_file.html', 
     *          'args' => array( ... ) )
     * )
     */
    public function route( $path, $args, $options = array() )
    {
        $router = kernel()->router;

        /* if args is string, it's a controller class */
        if( is_string($args)  ) 
        {
            /**
             * Extract action method name out, and set default to run method. 
             *
             *      FooController:index => array(FooController, indexAction)
             */
            $class = null;
            $action = 'indexAction';
            if( false !== ($pos = strrpos($args,':')) ) {
                list($class,$action) = explode(':',$args);
                if( false === strrpos( $action , 'Action' ) )
                    $action .= 'Action';
            }
            else {
                $class = $args;
            }


            /**
             * If it's not full-qualified classname, we should prepend our base namespace. 
             */
            if( 0 !== strpos( $class , '\\' ) )  {
                $class = $this->getNamespace() . "\\Controller\\$class";
            }

            if( ! method_exists($class,$action) ) {
                $action = 'run';
            }

            // $args = $class . ':' . $action;
            $cb = array($class, $action);
            $router->add( $path, $cb, $options );
        }
        elseif( is_array($args) ) 
        {
            // call template controller
            if( isset($args['template']) ) {
                $options['args'] = array( 
                    'template' => $args['template'],
                    'args' => @$args['args'],
                );
                $router->add( $path , 'Phifty\Routing\TemplateController' , $options );
            }
            elseif( isset($args['controller']) ) {
                $router->add( $path , $args['controller'], $options );
            }
        }
        else {
            throw new Exception( "Unkown route argument." );
        }
    }

    public function expandRoute($path,$class)
    {
        $routes = $class::expand();
        kernel()->router->mount( $path , $routes );
    }

    /**
     * Register/Generate CRUD actions 
     *
     * @param string $model model class
     * @param array  $types action types (Create, Update, Delete...)
     */
    public function withCRUDAction( $model , $types )
    {
        kernel()->action->registerCRUD( $this->getNamespace() , $model , (array) $types );
    }

    public function getWebDir() 
    {
        return $this->locate() . DS . 'web';
    }

    public function getTemplateDir()
    {
        return $this->locate() . DS . 'template';
    }

    public function getAssetDirs()
    {
        $dir = $this->locate();
        $assetDir = $dir . DIRECTORY_SEPARATOR . 'assets';

        $dirs = array();
        if( file_exists($assetDir) && $handle = opendir($assetDir) ) {
            while (false !== ($entry = readdir($handle))) {
                if( '.' === $entry || '..' === $entry ) 
                    continue;
                $path = $assetDir . DIRECTORY_SEPARATOR . $entry;
                if( is_dir($path) )
                    $dirs[] = $path;
            }
            closedir($handle);
        }
        return $dirs;
    }
}
