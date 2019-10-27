<?php

namespace User;

use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Permissions\Acl\AclInterface;
use Zend\Router\Http\Literal;

return [
    'router' => [
        'routes' => [
            'auth' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/auth',
                    'defaults' => [
                        'controller' => Controller\AuthController::class,
                    ],
                ],
                'child_routes' => [
                    'login' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/login',
                            'defaults' => [
                                'action' => 'login',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'logout' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/logout',
                            'defaults' => [
                                'action' => 'logout',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
            'user' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/user',
                    'defaults' => [
                        'controller' => Controller\UserController::class,
                    ],
                ],
                'child_routes' => [
                    'index' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/',
                            'defaults' => [
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\AuthController::class => Controller\Factory\AuthControllerFactory::class,
            Controller\UserController::class => Controller\Factory\UserControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
            Controller\Plugin\Auth::class => Controller\Plugin\Auth::class,
        ],
        'aliases' => [
            'auth' => Controller\Plugin\Auth::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            AclInterface::class                   => AclFactory::class,
            AuthenticationServiceInterface::class => AuthenticationServiceFactory::class,
            Model\UserTable::class                => Model\Factory\UserTableFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'view_helpers' => [
        'factories' => [
            View\Helper\LogInOrOut::class => View\Helper\Factory\LogInOrOutFactory::class,
        ],
        'aliases' => [
            'logInOrOut' => View\Helper\LogInOrOut::class,
        ],
    ],
];
