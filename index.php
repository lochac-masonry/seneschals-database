<?php

$startTime = microtime(true);

define('SENDB_VERSION', '1.6.0');

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Register autoloaders.
require_once(APPLICATION_PATH . '/autoload.php');
require_once(__DIR__ . '/vendor/autoload.php');

// Load config.
$config = new Zend_Config_Ini('config.ini', APPLICATION_ENV);

$authLevel = 'anyone';
function authenticate()
{
    global $authLevel;
    global $config;
    $db = Zend_Db_Table::getDefaultAdapter();

    $groupList = $db->fetchPairs("SELECT id, groupname FROM scagroup");
    foreach ($groupList as $id => $groupname) {
        $groupList[$id] = strtolower(str_replace(' ', '', $groupname));
    }

    if (isset($_GET['bypass'])
      && $_GET['bypass'] == 'true') {
        $auth['level'] = 'anyone';
    } elseif (isset($_SERVER['PHP_AUTH_USER'])
      && ($_SERVER['PHP_AUTH_USER'] == $config->auth->admin->username)
      && (hash('sha256', $_SERVER['PHP_AUTH_PW']) == $config->auth->admin->passhash)) {
        $auth['level'] = 'admin';
        $auth['id'] = 1;
    } elseif (isset($_SERVER['PHP_AUTH_USER'])
      && ($_SERVER['PHP_AUTH_USER'] == $config->auth->wheel->username)
      && (hash('sha256', $_SERVER['PHP_AUTH_PW']) == $config->auth->wheel->passhash)) {
        $auth['level'] = 'admin';
        $auth['id'] = 1;
    } elseif (isset($_SERVER['PHP_AUTH_USER'])
      && (in_array($_SERVER['PHP_AUTH_USER'], $groupList))
      && (hash('sha256', $_SERVER['PHP_AUTH_PW']) == $config->auth->user->passhash)) {
        $auth['level'] = 'user';
        $auth['id'] = array_search($_SERVER['PHP_AUTH_USER'], $groupList);
    } elseif (isset($_SERVER['PHP_AUTH_USER'])
      && ($_SERVER['PHP_AUTH_USER'] == 'guest')) {
        $auth['level'] = 'anyone';
    } else {
        Header('WWW-Authenticate: Basic realm="Seneschals\' Database"');
        $auth['level'] = 'anyone';
    }

    $authLevel = $auth['level'];
    return $auth;
}

function buildMenu()
{
    global $authLevel;
    // Set up main menu based on authLevel. All cases fall through to build an authLevel-cumulative menu.
    // Links are relative to application root.
    switch ($authLevel) {
        case 'admin':
            $menu[] = array(
                'link' => array('controller' => 'group', 'action' => 'edit'),
                'name' => 'Edit Group Details'
            );
            $menu[] = array(
                'link' => array('controller' => 'group', 'action' => 'close'),
                'name' => 'Close a Group'
            );
            $menu[] = array(
                'link' => array('controller' => 'postcode', 'action' => 'assign'),
                'name' => 'Assign Postcodes'
            );
            $menu[] = array(
                'link' => array('controller' => 'postcode', 'action' => 'upload'),
                'name' => 'Upload Postcodes File'
            );
            $menu[] = array(
                'link' => array('controller' => 'group', 'action' => 'domains'),
                'name' => 'Manage Group Email Domains'
            );
            // Fall through
        case 'user':
            $menu[] = array(
                'link' => array('controller' => 'group', 'action' => 'aliases'),
                'name' => 'Manage Group Email Aliases'
            );
            $menu[] = array(
                'link' => array('controller' => 'report', 'action' => null),
                'name' => 'Quarterly Reports'
            );
            $menu[] = array(
                'link' => array('controller' => 'event', 'action' => 'list'),
                'name' => 'Event List'
            );
            $menu[] = array(
                'link' => array('controller' => 'group', 'action' => 'baron-baroness'),
                'name' => 'Baron and Baroness Details'
            );
            // Fall through
        default: // Equivalent to case 'anyone'
            $menu[] = array(
                'link' => array('controller' => 'postcode', 'action' => null),
                'name' => 'Postcode Query'
            );
            $menu[] = array(
                'link' => array('controller' => 'group', 'action' => null),
                'name' => 'Group Roster'
            );
            $menu[] = array(
                'link' => array('controller' => 'event', 'action' => null),
                'name' => 'Submit Event Proposal'
            );
            break;
    }

    return $menu;
}

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    'config.ini'
);
$application->bootstrap()
            ->run();

$endTime = microtime(true);

Zend_Db_Table::getDefaultAdapter()->insert(
    'accessLog',
    array(
        'requestDateTime' => date('Y-m-d H:i:s', $startTime),
        'elapsedMs'       => ($endTime - $startTime) * 1000,
        'requestMethod'   => $_SERVER['REQUEST_METHOD'],
        'requestUri'      => strtok($_SERVER['REQUEST_URI'], '?'),
        'queryString'     => $_SERVER['QUERY_STRING']
    )
);
