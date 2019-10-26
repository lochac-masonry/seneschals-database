<?php

namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class ArrayIndex extends AbstractPlugin
{
    public function __invoke($array, $keyField, $valueField)
    {
        $result = [];
        foreach ($array as $item) {
            $result[$item[$keyField]] = $item[$valueField];
        }
        return $result;
    }
}
