<?php

namespace Application;

use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Router\Http\Segment;
use Zend\Session\Storage\SessionArrayStorage;
use Zend\Session\Validator\HttpUserAgent;
use Zend\Session\Validator\RemoteAddr;

return [
    'router' => [
        'routes' => [
            'default' => [
                'type'    => Segment::class,
                'options' => [
                    'route'       => '/[:controller[/:action]]',
                    'constraints' => [
                        'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ],
                    'defaults' => [
                        'controller'    => 'index',
                        'action'        => 'index',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'aliases' => [
            'auth'     => Controller\AuthController::class,
            'event'    => Controller\EventController::class,
            'group'    => Controller\GroupController::class,
            'index'    => Controller\IndexController::class,
            'postcode' => Controller\PostcodeController::class,
            'report'   => Controller\ReportController::class,
            'tools'    => Controller\ToolsController::class,
        ],
        'factories' => [
            Controller\AuthController::class     => Controller\BaseControllerFactory::class,
            Controller\EventController::class    => Controller\BaseControllerFactory::class,
            Controller\GroupController::class    => Controller\BaseControllerFactory::class,
            Controller\IndexController::class    => Controller\BaseControllerFactory::class,
            Controller\PostcodeController::class => Controller\BaseControllerFactory::class,
            Controller\ReportController::class   => Controller\BaseControllerFactory::class,
            Controller\ToolsController::class    => Controller\BaseControllerFactory::class,
        ],
    ],
    'service_manager' => [
        'factories' => [
            AuthenticationServiceInterface::class => AuthenticationServiceFactory::class,
        ],
    ],
    'session_config' => [
        'cookie_httponly' => true,
        'cookie_lifetime' => 60 * 60 * 1,
        'gc_maxlifetime'  => 60 * 60 * 24 * 30,
    ],
    'session_manager' => [
        'validators' => [
            RemoteAddr::class,
            HttpUserAgent::class,
        ],
    ],
    'session_storage' => [
        'type' => SessionArrayStorage::class,
    ],
    'translator' => [
        // Value not important, but if it isn't set it tries to determine a
        // default locale using the intl extension which is not installed.
        'locale' => 'en_US',
    ],
    'view_manager' => [
        'doctype'             => 'HTML5',
        'not_found_template'  => 'error/404',
        'exception_template'  => 'error/index',
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
