<?php
return array(
    'router' => array(
        'routes' => array(
            'login' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/login',
                    'defaults' => array(
                        '__NAMESPACE__' => 'ZF2AuthAcl\Controller',
                        'controller' => 'Index',
                        'action' => 'index'
                    )
                )
            ),
            'logout' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/logout',
                    'defaults' => array(
                        '__NAMESPACE__' => 'ZF2AuthAcl\Controller',
                        'controller' => 'Index',
                        'action' => 'logout'
                    )
                )
            ),
            'admin' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/admin',
                    'defaults' => array(
                        'controller' => 'ZF2AuthAcl\Controller\UserManager',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true, //定义父路由可以独立使用
                'child_routes' => array(
                    'add' => array(
                        'type' => 'literal',
                        'options' => array(
                            'route' => '/add',
                            'defaults' => array(
                                'action' => 'add'
                            ),
                        ),
                    ),
                    'edit' => array(
                        'type' => 'literal',
                        'options' => array(
                            'route' => '/edit',
                            'defaults' => array(
                                'action' => 'edit',
                            ),
                        ),
                    ),
                    'deluser' => array(
                        'type' => 'literal',
                        'options' => array(
                            'route' => '/deluser',
                            'defaults' => array(
                                'action' => 'deluser',
                            ),
                        ),                        
                    ),
                    'findpass' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/findpass',
                            'defaults' => array(
                                'controller' => 'ZF2AuthAcl\Controller\UserManager',
                                'action' => 'findpass',
                            ),
                        ),
                    ),
                    'resetpass' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/resetpass',
                            'defaults' => array(
                                'controller' => 'ZF2AuthAcl\Controller\UserManager',
                                'action' => 'resetpass',
                            ),
                        ),                        
                    ),
                ),
            ),
            'rpnexus' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/rpnexus',
                    'defaults' => array(
                        'controller' => 'ZF2AuthAcl\Controller\UserManager',
                        'action' => 'rpnexus',
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'ZF2AuthAcl\Controller\Index' => 'ZF2AuthAcl\Controller\IndexController',
            'ZF2AuthAcl\Controller\UserManager' => 'ZF2AuthAcl\Controller\UserManagerController',
        )
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view'
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    )
);
