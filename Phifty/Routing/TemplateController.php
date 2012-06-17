<?php
namespace Phifty\Routing;
use Phifty\View\Engine;
use Roller\Controller;

class TemplateController extends Controller
{
    public $template;
    public $args;

    public function __construct($args) 
    {
        $this->template = $args['template'];
        $this->args = isset($args['args']) ? $args['args'] : null;
    }

    function run()
    {
        $template   = $this->template;
        $args       = $this->args;
        $engineType = kernel()->config('View.Backend');

        /* get template engine */
        $engine = Engine::createEngine( $engineType );
        $viewClass = kernel()->config('View.Class') ?: 'Phifty\View';
        $view = new $viewClass( $engine );
        if( $args ) {
            $view->assign( $args );
        }
        return $view->render( $template );
    }
}


