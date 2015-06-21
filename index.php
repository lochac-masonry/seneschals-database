<?php

$startTime = microtime(true);

define('SENDB_VERSION', '1.1.0');

// Determine path of the application root.
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/application'));

// Make sure PHP's include path will catch the application library.
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path()
)));

// register application autoloader and Zend autoloader
require_once(APPLICATION_PATH . '/autoload.php');
require_once('Zend/Loader.php');
Zend_Loader::registerAutoload();

// Load config.
$appMode = getenv('APP_MODE') ? getenv('APP_MODE') : 'staging';
$config = new Zend_Config_Ini('config.ini', $appMode);

// Connect to database.
$db = Zend_Db::factory($config->database);

function authenticate() {
    global $config;
    global $db;

    $groupList = $db->fetchPairs("SELECT id, groupname FROM scagroup");
    foreach($groupList as $id => $groupname) {
        $groupList[$id] = strtolower(str_replace(' ', '', $groupname));
    }

    if(isset($_GET['bypass'])
      && $_GET['bypass'] == 'true') {
        $auth['level'] = 'anyone';

    } elseif(isset($_SERVER['PHP_AUTH_USER'])
      && ($_SERVER['PHP_AUTH_USER'] == $config->auth->admin->username)
      && (hash('sha256', $_SERVER['PHP_AUTH_PW']) == $config->auth->admin->passhash)) {
        $auth['level'] = 'admin';
        $auth['id'] = 1;

    } elseif(isset($_SERVER['PHP_AUTH_USER'])
      && ($_SERVER['PHP_AUTH_USER'] == $config->auth->wheel->username)
      && (hash('sha256', $_SERVER['PHP_AUTH_PW']) == $config->auth->wheel->passhash)) {
        $auth['level'] = 'admin';
        $auth['id'] = 1;

    } elseif(isset($_SERVER['PHP_AUTH_USER'])
      && (in_array($_SERVER['PHP_AUTH_USER'],$groupList))
      && (hash('sha256', $_SERVER['PHP_AUTH_PW']) == $config->auth->user->passhash)) {
        $auth['level'] = 'user';
        $auth['id'] = array_search($_SERVER['PHP_AUTH_USER'], $groupList);

    } elseif(isset($_SERVER['PHP_AUTH_USER'])
      && ($_SERVER['PHP_AUTH_USER'] == 'guest')) {
        $auth['level'] = 'anyone';

    } else {
        Header('WWW-Authenticate: Basic realm="Seneschals\' Database"');
        $auth['level'] = 'anyone';
    }

    Zend_Layout::getMvcInstance()->authlevel = $auth['level'];
    return $auth;
}

function buildMenu($authlevel) {
    // Set up main menu based on authlevel. All cases fall through to build an authlevel-cumulative menu.
    // Links are relative to application root.
    switch($authlevel) {
        case 'admin':
            $menu[] = array(
                'link' => '/group/edit',
                'name' => 'Edit Group Details'
            );
            $menu[] = array(
                'link' => '/group/close',
                'name' => 'Close a Group'
            );
            $menu[] = array(
                'link' => '/postcode/assign',
                'name' => 'Assign Postcodes'
            );
            $menu[] = array(
                'link' => '/postcode/upload',
                'name' => 'Upload Postcodes File'
            );
            $menu[] = array(
                'link' => '/group/domains',
                'name' => 'Manage Group Email Domains'
            );
        case 'user':
            $menu[] = array(
                'link' => '/group/aliases',
                'name' => 'Manage Group Email Aliases'
            );
            $menu[] = array(
                'link' => '/report',
                'name' => 'Quarterly Reports'
            );
            $menu[] = array(
                'link' => '/event/list',
                'name' => 'Event List'
            );
            $menu[] = array(
                'link' => '/group/baron-baroness',
                'name' => 'Baron and Baroness Details'
            );
        default: // Equivalent to case 'anyone'
            $menu[] = array(
                'link' => '/postcode/query',
                'name' => 'Postcode Query'
            );
            $menu[] = array(
                'link' => '/group/roster',
                'name' => 'Group Roster'
            );
            $menu[] = array(
                'link' => '/event/new',
                'name' => 'Submit Event Proposal'
            );
            break;
    }

    return $menu;
}

// Set up the global layout.
$layoutOptions = array(
    'layoutPath' => APPLICATION_PATH . '/layouts/scripts',
    'layout'     => 'default'
);
Zend_Layout::startMvc($layoutOptions);
Zend_Layout::getMvcInstance()->relativeUrl = $config->get('relativeUrl','');
Zend_Layout::getMvcInstance()->authlevel = 'anyone';

// Go.
Zend_Controller_Front::run('application/controllers');

$endTime = microtime(true);

$db->insert(
    'accessLog',
    array(
        'requestDateTime' => date('Y-m-d H:i:s', $startTime),
        'elapsedMs'       => ($endTime - $startTime) * 1000,
        'requestMethod'   => $_SERVER['REQUEST_METHOD'],
        'requestUri'      => strtok($_SERVER['REQUEST_URI'], '?'),
        'queryString'     => $_SERVER['QUERY_STRING']
    )
);
