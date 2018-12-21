<?php
namespace ZF2AuthAcl;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Authentication\Adapter\DbTable as DbAuthAdapter;
use Zend\Session\Container;
use ZF2AuthAcl\Model\User;
use ZF2AuthAcl\Model\UserRole;
use ZF2AuthAcl\Model\RolePermissionTable;
use Zend\Authentication\AuthenticationService;
use ZF2AuthAcl\Model\Role;
use ZF2AuthAcl\Utility\Acl;
use ZF2AuthAcl\Utility\UserManager;
use Zend\Cache\StorageFactory;

class Module
{

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $eventManager->attach(MvcEvent::EVENT_DISPATCH, array(
            $this,
            'boforeDispatch'
        ), 100);
    }    
    
    public function boforeDispatch(MvcEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();
        $target = $event->getTarget();
        $serviceManager = $event->getApplication()->getServiceManager();
        
        
        $whiteList = array(
            'ZF2AuthAcl\Controller\IndexController-index',
            'ZF2AuthAcl\Controller\IndexController-logout',
            'ZF2AuthAcl\Controller\UserManagerController-findpass',
            'ZF2AuthAcl\Controller\UserManagerController-resetpass',
            'Blog\Controller\IndexController-index',
            'Blog\Controller\IndexController-classlist',
            'Blog\Controller\IndexController-viewblog',
        );
        
        $requestUri = $request->getRequestUri();
        $controller = $event->getRouteMatch()->getParam('controller');
        $controller .= "Controller";
        
        $action = $event->getRouteMatch()->getParam('action');
        
        $requestedResourse = $controller . "-" . $action;
        
        $session = new Container('User');
        
        if ($session->offsetExists('account')) {
            if ($requestedResourse == 'ZF2AuthAcl\Controller\IndexController-index') {
                $url = '/admin';
                $response->setHeaders($response->getHeaders()
                         ->addHeaderLine('Location', $url));
                $response->setStatusCode(302);
            } else {
                                
                $userRole = $session->offsetGet('roleName');               
                $acl = $serviceManager->get('Acl');
                $acl->initAcl();
                
                $status = $acl->isAccessAllowed($userRole, $controller, $action);
                if (! $status) {
                    $resourceAction = $serviceManager->get('AllResource-Action');
                    if(in_array($controller.'-'.$action, $resourceAction)){
                        $response->setStatusCode(403);
                        $response->sendHeaders();
                        die("permisson deny");
                    }else{                       
                        $response->setStatusCode(404);
                        $response->sendHeaders();
                    }                   
                }
            }
        } else {           
            if ($requestedResourse != 'ZF2AuthAcl\Controller\IndexController-index' && ! in_array($requestedResourse, $whiteList)) {
                /* $url = '/login';
                $response->setHeaders($response->getHeaders()                         
                         ->addHeaderLine('Location', $url));
                $response->setStatusCode(302); */
                $response->setStatusCode(403);
            }
            $response->sendHeaders();
        }
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                )
            )
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'AllResource' => function ($serviceManager){
                    $manager = $serviceManager->get('ModuleManager');
                    $modules = $manager->getLoadedModules();
                    $loadedModules      = array_keys($modules);
                    $skipActionsList    = array('notFoundAction', 'getMethodFromAction');
                
                    $moduleArray = array();
                    foreach ($loadedModules as $loadedModule) {
                        $moduleClass = '\\' .$loadedModule . '\Module';
                        $moduleObject = new $moduleClass;
                        $config = $moduleObject->getConfig();
                
                        $controllers = $config['controllers']['invokables'];
                        foreach ($controllers as $key => $moduleClass) {
                            $tmpArray = get_class_methods($moduleClass);
                            $controllerActions = array();
                            foreach ($tmpArray as $action) {
                                if (substr($action, strlen($action)-6) === 'Action' && !in_array($action, $skipActionsList)) {
                                    $controllerActions[] = $action;
                                }
                            }     
                            $moduleArray[]=$moduleClass;
                        }
                    }
                    return $moduleArray;
                },
                'AllResource-Action'=>function($serviceManager){
                    $manager = $serviceManager->get('ModuleManager');
                    $modules = $manager->getLoadedModules();
                    $loadedModules      = array_keys($modules);
                    $skipActionsList    = array('notFoundAction', 'getMethodFromAction');
                
                    $resourceAction = array();
                    foreach ($loadedModules as $loadedModule) {
                        $moduleClass = '\\' .$loadedModule . '\Module';
                        $moduleObject = new $moduleClass;
                        $config = $moduleObject->getConfig();
                
                        $controllers = $config['controllers']['invokables'];
                        foreach ($controllers as $key => $moduleClass) {
                            $tmpArray = get_class_methods($moduleClass);
                            foreach ($tmpArray as $action) {
                                if (substr($action, strlen($action)-6) === 'Action' && !in_array($action, $skipActionsList)) {
                                    $action = preg_replace('/Action/', '', $action);
                                    $resourceAction[] = $moduleClass.'-'.$action;
                                }
                            }
                        }
                    }
                    return $resourceAction;
                },
                'AuthService' => function ($serviceManager)
                {
                    $adapter = $serviceManager->get('Zend\Db\Adapter\Adapter');
                    $dbAuthAdapter = new DbAuthAdapter($adapter, 'users', 'account', 'password');
                    $auth = new AuthenticationService();
                    $auth->setAdapter($dbAuthAdapter);
                    return $auth;
                },
                'Acl' => function ($serviceManager)
                {
                    $resource = $serviceManager->get('AllResource');
                    return new Acl($resource);
                },
                'UserTable' => function ($serviceManager)
                {
                    return new User($serviceManager->get('Zend\Db\Adapter\Adapter'));
                },
                'RoleTable' => function ($serviceManager)
                {
                    return new Role($serviceManager->get('Zend\Db\Adapter\Adapter'));
                },
                'UserRoleTable' => function ($serviceManager)
                {
                    return new UserRole($serviceManager->get('Zend\Db\Adapter\Adapter'));
                },
                'RolePermissionTable' => function ($serviceManager)
                {
                    return new RolePermissionTable($serviceManager->get('Zend\Db\Adapter\Adapter'));
                },
                'UserManager' => function ($serviceManager){
                    $users = new UserManager();
                    $users->setServiceLocator($serviceManager);
                    return $users;
                },
                'Cache' => function ($serviceManager){
                    $cache = StorageFactory::factory(array(
                        'adapter' => array(
                            'name' => 'apc',
                            'options' => array(
                                'ttl' => 3600, //有效期为1个小时
                            ),
                            'plagins' => array(
                                'exception_handler' => array(
                                    'throw_exceptions' => false,
                                ),
                            ),
                        ),
                    ));
                    return $cache;
                }
            )
        );
    }
}