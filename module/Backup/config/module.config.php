<?php
return array(
    'service_manager' => array(
        'factories' => array(
            'Zend\DB\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Backup\Controller\Index' => 'Backup\Controller\IndexController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'backup' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/backup',
                    'defaults' => array(
                        'controller' => 'Backup\Controller\Index',
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