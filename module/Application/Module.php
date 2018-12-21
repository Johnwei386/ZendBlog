<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
          //设置php.ini，配置自己的配置项，配置项在global.php中
        $app = $e->getApplication();
        $sm = $app->getServiceManager();
        $config = $sm->get('Config');//得到全部merge之后的配置项
        $phpSettings = isset($config['phpSettings']) ? $config['phpSettings'] : array();
        if(!empty($phpSettings)) {
            foreach($phpSettings as $key => $value) {
                ini_set($key, $value);
            }
        }
        
          //自定义错误信息显示，使用翻译转换，error信息定义在languages文件夹中
        $translator = $e->getApplication()->getServiceManager()->get('MvcTranslator');
        $path1 = ROOT_PATH."/languages/zh/Zend_Validate.php";
        $path2 = ROOT_PATH."/languages/zh/Zend_Captcha.php";
        $translator->addTranslationFile('phpArray', $path1);
        $translator->addTranslationFile('phpArray', $path2);
        \Zend\Validator\AbstractValidator::setDefaultTranslator($translator);
        
        //捕获并处理不同的错误
        $eventManager->getSharedManager()->attach('*', MvcEvent::EVENT_DISPATCH, array($this, 'onDispatchError'), -100);
        $eventManager->getSharedManager()->attach('*', MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'onDispatchError'), -100);
        $eventManager->getSharedManager()->attach('*', MvcEvent::EVENT_RENDER_ERROR, array(
            $this,
            'onDispatchError'
        ), - 100);
        
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
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
    
    public function onDispatchError(MvcEvent $event){
        $response = $event->getResponse();
        if ($response->getStatusCode() == 403) {
            //DO SOMETHING            
            $vm = $event->getViewModel();
            $vm->setTemplate('error/403.phtml');
        }
        /* if($response->getStatusCode() == 404){
            $vm = $event->getViewModel();
            $vm->setTemplate('error/404de.phtml');
        } */
    }
}
