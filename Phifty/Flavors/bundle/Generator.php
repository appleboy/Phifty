<?php
namespace bundle;
use GenPHP\Flavor\BaseGenerator;

class Generator extends BaseGenerator
{
    public function brief() { return 'generate bundle structure'; }

    public function generate($bundleName)
    {
        $bundleDir = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'bundles' . DIRECTORY_SEPARATOR . $bundleName;

        $this->createDir( $bundleDir . DIRECTORY_SEPARATOR . 'Model' );
        $this->createDir( $bundleDir . DIRECTORY_SEPARATOR . 'Controller' );
        $this->createDir( $bundleDir . DIRECTORY_SEPARATOR . 'Action' );
        $this->createDir( $bundleDir . DIRECTORY_SEPARATOR . 'Template' );
        $this->createDir( $bundleDir . DIRECTORY_SEPARATOR . 'Assets' );

        $classFile = $bundleDir . DIRECTORY_SEPARATOR . $bundleName . '.php';
        $this->render('Plugin.php.twig', $classFile, array(
            'bundleName' => $bundleName,
        ));

        // registering bundle to config
        $config = yaml_parse(file_get_contents('config/framework.yml'));
        $config['Plugins'][ $bundleName ] = array();
        file_put_contents('config/framework.yml', yaml_emit($config) );
    }
}
