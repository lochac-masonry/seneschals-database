<?php

use Zend\Mvc\Application;
use Zend\Stdlib\ArrayUtils;

/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

// Decline static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server') {
    $path = realpath(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    if (__FILE__ !== $path && is_file($path)) {
        return false;
    }
    unset($path);
}

// Composer autoloading
include __DIR__ . '/../vendor/autoload.php';

if (!class_exists(Application::class)) {
    throw new RuntimeException(
        "Unable to load application.\n"
        . "- Type `composer install` if you are developing locally.\n"
        . "- Type `vagrant ssh -c 'composer install'` if you are using Vagrant.\n"
        . "- Type `docker-compose run zf composer install` if you are using Docker.\n"
    );
}

// Retrieve configuration
$appConfig = require __DIR__ . '/../config/application.config.php';
if (file_exists(__DIR__ . '/../config/development.config.php')) {
    $appConfig = ArrayUtils::merge($appConfig, require __DIR__ . '/../config/development.config.php');
}

$authLevel = 'anyone';
function buildMenu()
{
    global $authLevel;
    // Set up main menu based on authLevel. All cases fall through to build an authLevel-cumulative menu.
    // Links are relative to application root.
    switch ($authLevel) {
        case 'admin':
            $menu[] = [
                'link' => ['controller' => 'group', 'action' => 'edit'],
                'name' => 'Edit Group Details'
            ];
            $menu[] = [
                'link' => ['controller' => 'group', 'action' => 'close'],
                'name' => 'Close a Group'
            ];
            $menu[] = [
                'link' => ['controller' => 'postcode', 'action' => 'assign'],
                'name' => 'Assign Postcodes'
            ];
            $menu[] = [
                'link' => ['controller' => 'postcode', 'action' => 'upload'],
                'name' => 'Upload Postcodes File'
            ];
            $menu[] = [
                'link' => ['controller' => 'group', 'action' => 'domains'],
                'name' => 'Manage Group Email Domains'
            ];
            // Fall through
        case 'user':
            $menu[] = [
                'link' => ['controller' => 'group', 'action' => 'aliases'],
                'name' => 'Manage Group Email Aliases'
            ];
            $menu[] = [
                'link' => ['controller' => 'report', 'action' => null],
                'name' => 'Quarterly Reports'
            ];
            $menu[] = [
                'link' => ['controller' => 'event', 'action' => 'list'],
                'name' => 'Event List'
            ];
            $menu[] = [
                'link' => ['controller' => 'group', 'action' => 'baron-baroness'],
                'name' => 'Baron and Baroness Details'
            ];
            // Fall through
        default: // Equivalent to case 'anyone'
            $menu[] = [
                'link' => ['controller' => 'postcode', 'action' => null],
                'name' => 'Postcode Query'
            ];
            $menu[] = [
                'link' => ['controller' => 'group', 'action' => null],
                'name' => 'Group Roster'
            ];
            $menu[] = [
                'link' => ['controller' => 'event', 'action' => null],
                'name' => 'Submit Event Proposal'
            ];
            break;
    }

    return $menu;
}

// Run the application!
Application::init($appConfig)->run();
