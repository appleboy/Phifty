<?php
namespace Phifty\Command\CacheCommand;
use CLIFramework\Command;

class ClearCommand extends Command
{
    function brief() {  }

    function execute($args)
    {
        $logger = $this->getLogger();

        $logger->info( 'Cleaning up cache...' );

        $cache = webapp()->cache;
        $cache->clear();

        $logger->info( 'Done' );
    }
}

