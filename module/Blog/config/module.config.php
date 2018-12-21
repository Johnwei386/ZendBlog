<?php
return array(
    'service_manager' => array(
        'factories' => array(
            'Zend\DB\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Blog\Controller\Index' => 'Blog\Controller\IndexController',
            'Blog\Controller\List' => 'Blog\Controller\ListController',          
        ),
    ),
    'router' => array(
        'routes' => array(
            'blog' => array(
                'type'=>'Segment',
                'options' => array(
                    'route' => '/blog[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Blog\Controller\List',
                        'action' => 'index',
                    ),
                ),
            ),
            'classlist' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/classlist[/:category][/]',
                    'constraints' => array(
                        'category' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Blog\Controller\Index',
                        'action' => 'classlist',
                    ),
                ),
            ),
            'viewblog' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/viewblog[/:id][/]',
                    'constraints' => array(
                        'id' => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Blog\Controller\Index',
                        'action' => 'viewblog',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);