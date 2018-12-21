<?php
namespace Motto;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Motto\Model\Post;
use Motto\Model\PostTable;

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
              'Motto\Model\PostTable' => function($sm){
                  $tableGateway = $sm->get('MottoTableGateway');
                  $table = new PostTable($tableGateway);
                  return $table;
              }, 
              'MottoTableGateway' => function($sm){
                  $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                  $resultSetPrototype = new ResultSet();
                  $resultSetPrototype->setArrayObjectPrototype(new Post);
                  return new TableGateway('maxim', $dbAdapter,null,$resultSetPrototype);
              },
            ),
        );
    }
}