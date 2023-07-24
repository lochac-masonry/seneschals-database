<?php

/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

declare(strict_types=1);

use Laminas\Session\Storage\SessionArrayStorage;
use Laminas\Session\Validator\HttpUserAgent;
use Laminas\Session\Validator\RemoteAddr;

return [
    'clamd_socket' => 'unix:///var/run/clamav/clamd.ctl',
    'session_config' => [
        'cookie_httponly' => true,
        'cookie_lifetime' => 60 * 90,
        // This has no effect as garbage collection is performed by a cronjob based on the global config.
        // 'gc_maxlifetime'  => 60 * 60 * 24 * 30,
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
    ],

    // All emails are sent From this address.
    'fromEmail' => '"Lochac Seneschals\' Database" <seneschaldb@lochac.sca.org>',
];
