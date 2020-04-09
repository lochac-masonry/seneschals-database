<?php

declare(strict_types=1);

namespace User;

use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Permissions\Acl\AclInterface;
use Laminas\Router\Http\Literal;

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
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\AuthController::class => Controller\Factory\AuthControllerFactory::class,
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
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
