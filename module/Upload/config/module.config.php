<?php
return array(
    'service_manager' => array(
        'factories' => array(
            'Zend\DB\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Upload\Controller\Upfile' => 'Upload\Controller\UpfileController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'upload' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/upload',
                    'defaults' => array(
                        'controller' => 'Upload\Controller\Upfile',
                        'action' => 'upload',
                    ),
                ),
            ),
            'modhead' => array(
                'type' => 'Literal',
                'options' => array(
                  'route' => '/modhead',
                    'defaults' => array(
                        'controller' => 'Upload\Controller\Upfile',
                        'action' => 'modhead',
                    ),
                ),
            ),
            'upfiles' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/upfiles',
                    'defaults' => array(
                        'controller' => 'Upload\Controller\Upfile',
                        'action' => 'upfileBrowser',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'rename' => array(
                        'type' => 'literal',
                        'options' => array(
                            'route' => '/rename',
                            'defaults' => array(
                                'action' => 'rename',
                            ),
                        ),
                    ),
                    'delfile' => array(
                        'type' => 'literal',
                        'options' => array(
                            'route' => '/delfile',
                            'defaults' => array(
                                'action' => 'delfile',
                            ),
                        ),
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