<?php
namespace Blog;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Blog\Model\Post;
use Blog\Model\PostTable;

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
              'Blog\Model\PostTable' => function($sm){
                  $tableGateway = $sm->get('PostTableGateway');
                  $table = new PostTable($tableGateway);
                  return $table;
              }, 
              'PostTableGateway' => function($sm){
                  $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                  $resultSetPrototype = new ResultSet();
                  $resultSetPrototype->setArrayObjectPrototype(new Post);
                  return new TableGateway('blog', $dbAdapter,null,$resultSetPrototype);
              },
            ),
        );
    }
}