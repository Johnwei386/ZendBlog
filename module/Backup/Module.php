<?php
namespace Backup;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Db\Metadata\Metadata;

class Module implements AutoloaderProviderInterface,ConfigProviderInterface
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'DatabaseMeta' => function ($serviceManager)
                {
                    return new Metadata($serviceManager->get('Zend\Db\Adapter\Adapter'));
                },
                'DbAdapter' => function($serviceManager){
                  return $serviceManager->get('Zend\Db\Adapter\Adapter');
                },
            )
        );
    }
}