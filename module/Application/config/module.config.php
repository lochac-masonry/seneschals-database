<?php

declare(strict_types=1);

namespace Application;

use Laminas\Router\Http\{Literal, Segment};

return [
    'router' => [
        'routes' => [
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
            ],
            'event' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/event',
                    'defaults' => [
                        'controller' => Controller\EventController::class,
                    ],
                ],
                'child_routes' => [
                    'attachment' => [
                        'type'    => Segment::class,
                        'options' => [
                            'route'       => '/attachment/:id',
                            'constraints' => [
                                'id' => '[0-9]+',
                            ],
                        ],
                        'child_routes' => [
                            'delete' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/delete',
                                    'defaults' => [
                                        'action' => 'deleteAttachment',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                            'download' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/download',
                                    'defaults' => [
                                        'action' => 'downloadAttachment',
                                    ],
                                ],
                                'may_terminate' => true,
                            ],
                        ],
                    ],
                    'edit' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/edit',
                            'defaults' => [
                                'action' => 'edit',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'list' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/list',
                            'defaults' => [
                                'action' => 'list',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'new' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/new',
                            'defaults' => [
                                'action' => 'new',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
            'group' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/group',
                    'defaults' => [
                        'controller' => Controller\GroupController::class,
                    ],
                ],
                'child_routes' => [
                    'aliases' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/aliases',
                            'defaults' => [
                                'action' => 'aliases',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'close' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/close',
                            'defaults' => [
                                'action' => 'close',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'domains' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/domains',
                            'defaults' => [
                                'action' => 'domains',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'edit' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/edit',
                            'defaults' => [
                                'action' => 'edit',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
            'postcode' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/postcode',
                    'defaults' => [
                        'controller' => Controller\PostcodeController::class,
                    ],
                ],
                'child_routes' => [
                    'assign' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/assign',
                            'defaults' => [
                                'action' => 'assign',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'query' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/query',
                            'defaults' => [
                                'action' => 'query',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'upload' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/upload',
                            'defaults' => [
                                'action' => 'upload',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
            'report' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/report',
                    'defaults' => [
                        'controller' => Controller\ReportController::class,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'sample-barony' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/sample-barony',
                            'defaults' => [
                                'action' => 'sample-barony',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'sample-canton' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/sample-canton',
                            'defaults' => [
                                'action' => 'sample-canton',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'sample-college' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/sample-college',
                            'defaults' => [
                                'action' => 'sample-college',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'sample-shire' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/sample-shire',
                            'defaults' => [
                                'action' => 'sample-shire',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
            'tools' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/tools',
                    'defaults' => [
                        'controller' => Controller\ToolsController::class,
                    ],
                ],
                'child_routes' => [
                    'keep-alive' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/keep-alive',
                            'defaults' => [
                                'action' => 'keep-alive',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                    'version' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/version',
                            'defaults' => [
                                'action' => 'version',
                            ],
                        ],
                        'may_terminate' => true,
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'invokables' => [
            Controller\ToolsController::class => Controller\ToolsController::class,
        ],
        'factories' => [
            Controller\EventController::class    => Controller\EventControllerFactory::class,
            Controller\GroupController::class    => Controller\DatabaseControllerFactory::class,
            Controller\IndexController::class    => Controller\DatabaseControllerFactory::class,
            Controller\PostcodeController::class => Controller\DatabaseControllerFactory::class,
            Controller\ReportController::class   => Controller\DatabaseControllerFactory::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
            Controller\Plugin\Alert::class      => Controller\Plugin\Alert::class,
            Controller\Plugin\ArrayIndex::class => Controller\Plugin\ArrayIndex::class,
            Controller\Plugin\CurrentUrl::class => Controller\Plugin\CurrentUrl::class,
        ],
        'factories' => [
            Controller\Plugin\SendEmail::class => Controller\Plugin\Factory\SendEmailFactory::class,
        ],
        'aliases' => [
            'alert'      => Controller\Plugin\Alert::class,
            'arrayIndex' => Controller\Plugin\ArrayIndex::class,
            'currentUrl' => Controller\Plugin\CurrentUrl::class,
            'sendEmail'  => Controller\Plugin\SendEmail::class,
        ],
    ],
    'view_helpers' => [
        'invokables' => [
            View\Helper\FormatDate::class       => View\Helper\FormatDate::class,
            View\Helper\InsertLinebreaks::class => View\Helper\InsertLinebreaks::class,
        ],
        'aliases' => [
            'formatDate'       => View\Helper\FormatDate::class,
            'insertLinebreaks' => View\Helper\InsertLinebreaks::class,
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
                'label' => 'Database',
                'route' => 'home',
                'class' => 'navigation__link--large',
            ],
            [
                'label'     => 'Edit Group Details',
                'route'     => 'group/edit',
                'resource'  => 'group',
                'privilege' => 'edit',
            ],
            [
                'label'     => 'Close a Group',
                'route'     => 'group/close',
                'resource'  => 'group',
                'privilege' => 'close',
            ],
            [
                'label'     => 'Assign Postcodes',
                'route'     => 'postcode/assign',
                'resource'  => 'postcode',
                'privilege' => 'assign',
            ],
            [
                'label'     => 'Upload Postcodes File',
                'route'     => 'postcode/upload',
                'resource'  => 'postcode',
                'privilege' => 'upload',
            ],
            [
                'label'     => 'Manage Group Email Domains',
                'route'     => 'group/domains',
                'resource'  => 'group',
                'privilege' => 'manage_domains',
            ],
            [
                'label'     => 'Manage Group Email Aliases',
                'route'     => 'group/aliases',
                'resource'  => 'group',
                'privilege' => 'manage_aliases',
            ],
            [
                'label'     => 'Quarterly Reports',
                'route'     => 'report',
                'resource'  => 'report',
                'privilege' => 'submit',
            ],
            [
                'label'     => 'Event List',
                'route'     => 'event/list',
                'resource'  => 'event',
                'privilege' => 'list',
            ],
            [
                'label'     => 'Postcode Query',
                'route'     => 'postcode/query',
                'resource'  => 'postcode',
                'privilege' => 'list',
            ],
            [
                'label'     => 'Submit Event Proposal',
                'route'     => 'event/new',
                'resource'  => 'event',
                'privilege' => 'create',
            ],
            [
                'label'     => 'Log Out',
                'route'     => 'auth/logout',
                'class'     => 'navigation__link--large',
                'resource'  => 'auth',
                'privilege' => 'logout',
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            LazyQuahogClient::class => LazyQuahogClientFactory::class,
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
