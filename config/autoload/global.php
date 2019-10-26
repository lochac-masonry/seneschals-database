<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

use Zend\Session\Storage\SessionArrayStorage;
use Zend\Session\Validator\HttpUserAgent;
use Zend\Session\Validator\RemoteAddr;

return [
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
    ],
];
