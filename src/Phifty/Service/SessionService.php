<?php
namespace Phifty\Service;
use SessionKit;

class SessionService
    implements ServiceRegister
{

    public function getId() { return 'Session'; }

    public function register($kernel, $options = array())
    {
        // XXX: customize this for $options
        $kernel->session = function() {
            $session = new SessionKit\Session(array(
                'state'   => new SessionKit\State\NativeState,
                'storage' => new SessionKit\Storage\NativeStorage,
            ));

            return $session;
        };
    }
}
