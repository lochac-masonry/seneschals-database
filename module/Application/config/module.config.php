<?php

namespace Application;

use Zend\Router\Http\Segment;

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
            'event'    => Controller\EventController::class,
            'group'    => Controller\GroupController::class,
            'index'    => Controller\IndexController::class,
            'postcode' => Controller\PostcodeController::class,
            'report'   => Controller\ReportController::class,
            'tools'    => Controller\ToolsController::class,
        ],
        'factories' => [
            Controller\EventController::class    => Controller\BaseControllerFactory::class,
            Controller\GroupController::class    => Controller\BaseControllerFactory::class,
            Controller\IndexController::class    => Controller\BaseControllerFactory::class,
            Controller\PostcodeController::class => Controller\BaseControllerFactory::class,
            Controller\ReportController::class   => Controller\BaseControllerFactory::class,
            Controller\ToolsController::class    => Controller\BaseControllerFactory::class,
        ],
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
