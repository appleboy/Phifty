<?php
namespace Phifty\Service;
use Phifty\Locale;

class LocaleService
    implements ServiceInterface
{

    public function register($kernel, $options = array() )
    {
        // call spl autoload, to load `__` i18n function
        class_exists('Phifty\Locale', true);

        $config = $kernel->config->get('framework','i18n');
        if( $config->isEmpty() )
            return;

        $textdomain =  $kernel->config->get('framework','name');
        $defaultLang  = $config->default ?: 'en';
        $localeDir = $config->localedir;

        if( ! ( $textdomain && $defaultLang && $localeDir) ) {
            return;
        }

        $locale = new Locale;
        $locale->setDefault( $defaultLang );
        $locale->domain( $textdomain ); # use application id for domain name.
        $locale->localedir( $kernel->rootDir . DIRECTORY_SEPARATOR . $localeDir);

        // add languages to list
        foreach( @$config->lang as $localeName ) {
            $locale->add( $localeName );
        }

        $locale->init();
        # _('en');
        
        $kernel->locale = function() use ($locale) {
            return $locale;
        };
    }
}
