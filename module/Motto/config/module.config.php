<?php
return array(
    'service_manager' => array(
        'factories' => array(
            'Zend\DB\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Motto\Controller\Index' => 'Motto\Controller\IndexController',          
        ),
    ),
    'router' => array(
        'routes' => array(
            'motto' => array(
                'type'=>'Segment',
                'options' => array(
                    'route' => '/motto[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Motto\Controller\Index',
                        'action' => 'index',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);