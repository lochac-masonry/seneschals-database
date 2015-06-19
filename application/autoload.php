<?php

function lochac_sendb_autoload($className)
{
    $classPath = explode('_', $className);
    if ($classPath[0] != 'SenDb') {
        return;
    }

    // Drop 'SenDb'
    $classPath = array_slice($classPath, 1);

    // Look for class of type 'Thing' in directory 'things'
    if(count($classPath) > 1) {
        $classPath[0] = strtolower($classPath[0]) . 's';
    }

    $filePath = dirname(__FILE__) . '/' . implode('/', $classPath) . '.php';
    if (file_exists($filePath)) {
        require_once($filePath);
    }
}

spl_autoload_register('lochac_sendb_autoload');
