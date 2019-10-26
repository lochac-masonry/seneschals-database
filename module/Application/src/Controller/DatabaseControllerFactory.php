<?php

namespace Application\Controller;

use Interop\Container\ContainerInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class DatabaseControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $db = $container->get(AdapterInterface::class);
        return new $requestedName($db);
    }
}
