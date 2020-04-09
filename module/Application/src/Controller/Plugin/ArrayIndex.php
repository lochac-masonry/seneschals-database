<?php

declare(strict_types=1);

namespace Application\Controller\Plugin;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

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
