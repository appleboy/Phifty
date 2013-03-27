<?php
namespace Phifty\Command;
use CLIFramework\Command;
use Symfony\Component\Finder\Finder;
use Phifty\Kernel;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Phifty;


/*
function proc_read($command)
{
    $output = '';
    $h = popen($command,'r');
    while (!feof($h)) {
        $content .= fread($h, 1024);
    }
    pclose($h);
    return $output;
}
*/

function remove_cwd($path)
{
    return substr($path, strlen(getcwd()) + 1 );
}


/**
 *
 * 1. create dictionary from locale files (po files)
 * 2. scan PHP files and look for _( ) and __( ) pattern
 * 3. build & scan twig templates
 * 4. rewrite po files
 *
 */
class LocaleCommand extends Command
{

    public function options($opts)
    {
        $opts->add('f|force','force');
    }


    public function execute()
    {
        $kernel = kernel();
        $localeDir = $kernel->config->get('framework','Services.LocaleService.Directory') ?: 'locale';
        $frameworkLocaleDir = PH_ROOT . DIRECTORY_SEPARATOR . 'locale';
        $langs     = $kernel->config->get('framework','Services.LocaleService.Langs')->config;

        $cwd = getcwd();
        $appPoFiles = array();
        $frameworkId = Kernel::FRAMEWORK_ID;
        $appId       = $kernel->config->framework->ApplicationID;

        $frameworkPoFilename = $frameworkId . '.po';
        $appPoFilename       = $appId . '.po';

        // prepare po files from framework po source files.
        foreach( $langs as $langId ) {
            $poDir        = $localeDir . DIRECTORY_SEPARATOR . $langId . DIRECTORY_SEPARATOR . 'LC_MESSAGES';
            $sourcePoPath = $frameworkLocaleDir . DIRECTORY_SEPARATOR . $langId . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . $frameworkId . '.po';
            $targetPoPath = $localeDir . DIRECTORY_SEPARATOR . $langId . DIRECTORY_SEPARATOR . 'LC_MESSAGES' . DIRECTORY_SEPARATOR . $appId . '.po';

            if ( ! file_exists($poDir) ) {
                mkdir($poDir, 0755, true);
            }

            if ( $this->options->force || file_exists( $sourcePoPath ) && ! file_exists( $targetPoPath ) ) {
                $this->logger->info("Creating $targetPoPath");

                if ( $sourcePoPath != $targetPoPath ) {
                    copy($sourcePoPath, $targetPoPath);
                }
            }
        }

        // Compile templates from plugins
        $this->logger->info("Compiling templates...");
        $engine = new Phifty\View\Twig;
        $twig = $engine->getRenderer();
        foreach( $kernel->plugins as $plugin ) {
            $pluginDir = $plugin->locate();
            $templateDir = $plugin->getTemplateDir();
            if ( ! file_exists($templateDir) ) {
                continue;
            }
            foreach (new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($templateDir),
                    RecursiveIteratorIterator::LEAVES_ONLY) as $file) 
            {
                // force compilation
                if( preg_match( '/\.(html?|twig)$/', $file ) ) {
                    $this->logger->info("Compiling " . remove_cwd($file->getPathname()) ,1);
                    $twig->loadTemplate( substr($file, strlen(dirname($pluginDir)) + 1) );
                }
            }
        }

        $potFile = 'locale/messages.pot';
        touch($potFile);

        $scanDirs = array();
        $scanDirs[] = PH_ROOT . DIRECTORY_SEPARATOR . 'src'; // phifty src
        $scanDirs[] = PH_ROOT . DIRECTORY_SEPARATOR . 'applications'; // phifty applications
        $scanDirs[] = PH_ROOT . DIRECTORY_SEPARATOR . 'bundles';
        $scanDirs[] = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'applications';
        $scanDirs[] = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'bundles';
        $scanDirs[] = $kernel->getCacheDir();
        $scanDirs = array_filter( $scanDirs, 'file_exists' );

        $cmd = sprintf("xgettext -j -o %s --from-code=UTF-8 -n -L PHP -D $(find %s -type f -iname '*.php')",
            $potFile,
            join( ' ', $scanDirs ) );

        $this->logger->info($cmd);
        system($cmd, $retval);
        if ( $retval != 0 )
            die('xgettext error');


        // Update message catalog
        $finder = Finder::create()->files()->name('*.po')->in( $localeDir );
        foreach ( $finder->getIterator() as $file ) {
            $shortPath     = substr( $file->getPath() , strlen($cwd) + 1 );
            $shortPathname = substr( $file->getPathname() , strlen($cwd) + 1 );

            $this->logger->info("Updating $shortPathname",1);
            $cmd = sprintf('msgmerge %s %s', $shortPathname, $potFile);
            $this->logger->debug($cmd,1);
            system($cmd, $retval);
            if ( $retval != 0 )
                die('xgettext error');

            $this->logger->info("Compiling messages $shortPathname",1);
            $cmd = sprintf('msgfmt -v %s', $shortPathname);
            $this->logger->debug($cmd,1);
            system($cmd, $retval);
            if ( $retval != 0 )
                die('xgettext error');
        }
    }

}
