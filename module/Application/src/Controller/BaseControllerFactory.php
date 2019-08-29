<?php

namespace Application\Controller;

use Interop\Container\ContainerInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class BaseControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        $db = $container->get(AdapterInterface::class);
        return new $requestedName($config, $db);
    }
}
