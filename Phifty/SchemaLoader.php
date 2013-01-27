<?php
$finder = new LazyRecord\Schema\SchemaFinder;
foreach( kernel()->applications as $app ) {
    $finder->addPath( $app->locate() );
}

foreach( kernel()->plugins->getPlugins() as $plugin ) {
    $finder->addPath( $plugin->locate() );
}

# $finder->addPath( PH_ROOT . DIRECTORY_SEPARATOR . 'src' );
$finder->addPath( PH_ROOT . DIRECTORY_SEPARATOR . 'tests' );
$finder->loadFiles();
return $finder->getSchemas();
