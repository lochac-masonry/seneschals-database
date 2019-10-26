<?php

namespace Application;

use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Permissions\Acl\AclInterface;
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
    'navigation' => [
        'default' => [
            [
                'label' => 'Lochac Homepage',
                'uri'   => 'https://lochac.sca.org/lochac',
                'class' => 'navigation__link--large',
            ],
            [
                'label' => 'From the Seneschal (Home)',
                'uri'   => 'https://seneschal.lochac.sca.org',
                'class' => 'navigation__link--large',
            ],
            [
                'label'      => 'Database',
                'controller' => 'index',
                'class'      => 'navigation__link--large',
            ],
            [
                'label'      => 'Edit Group Details',
                'controller' => 'group',
                'action'     => 'edit',
                'resource'   => 'group',
                'privilege'  => 'edit',
            ],
            [
                'label'      => 'Close a Group',
                'controller' => 'group',
                'action'     => 'close',
                'resource'   => 'group',
                'privilege'  => 'close',
            ],
            [
                'label'      => 'Assign Postcodes',
                'controller' => 'postcode',
                'action'     => 'assign',
                'resource'   => 'postcode',
                'privilege'  => 'assign',
            ],
            [
                'label'      => 'Upload Postcodes File',
                'controller' => 'postcode',
                'action'     => 'upload',
                'resource'   => 'postcode',
                'privilege'  => 'upload',
            ],
            [
                'label'      => 'Manage Group Email Domains',
                'controller' => 'group',
                'action'     => 'domains',
                'resource'   => 'group',
                'privilege'  => 'manage_domains',
            ],
            [
                'label'      => 'Manage Group Email Aliases',
                'controller' => 'group',
                'action'     => 'aliases',
                'resource'   => 'group',
                'privilege'  => 'manage_aliases',
            ],
            [
                'label'      => 'Quarterly Reports',
                'controller' => 'report',
                'resource'   => 'report',
                'privilege'  => 'submit',
            ],
            [
                'label'      => 'Event List',
                'controller' => 'event',
                'action'     => 'list',
                'resource'   => 'event',
                'privilege'  => 'list',
            ],
            [
                'label'      => 'Baron and Baroness Details',
                'controller' => 'group',
                'action'     => 'baron-baroness',
                'resource'   => 'group',
                'privilege'  => 'update_nobility',
            ],
            [
                'label'      => 'Postcode Query',
                'controller' => 'postcode',
                'action'     => 'query',
                'resource'   => 'postcode',
                'privilege'  => 'list',
            ],
            [
                'label'      => 'Group Roster',
                'controller' => 'group',
                'action'     => 'roster',
                'resource'   => 'group',
                'privilege'  => 'list',
            ],
            [
                'label'      => 'Submit Event Proposal',
                'controller' => 'event',
                'action'     => 'new',
                'resource'   => 'event',
                'privilege'  => 'create',
            ],
            [
                'label'      => 'Log Out',
                'controller' => 'auth',
                'action'     => 'logout',
                'class'      => 'navigation__link--large',
                'resource'   => 'auth',
                'privilege'  => 'logout',
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            AclInterface::class                   => AclFactory::class,
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
