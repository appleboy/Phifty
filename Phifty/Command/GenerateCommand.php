<?php
namespace Phifty\Command;
use CLIFramework\Command;

class GenerateCommand extends Command
{
    public function brief() { return 'template generator command'; }

    public function execute($flavor)
    {
        $args = func_get_args();
        array_shift($args);

        $loader = new \GenPHP\Flavor\FlavorLoader(array(
            PH_ROOT . '/src/Phifty/Flavors'
        ));
        if ( $flavor = $loader->load($flavor) ) {
            $generator = $flavor->getGenerator();
            $generator->setLogger($this->logger);
            $runner = new \GenPHP\GeneratorRunner;
            $runner->run($generator,$args);
        } else {
            throw new Exception("Flavor $flavor not found.");
        }
        $this->logger->info('Done');
    }
}
