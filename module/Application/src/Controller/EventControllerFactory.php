<?php

namespace Application\Controller;

use Application\LazyQuahogClient;
use Interop\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class EventControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new $requestedName(
            $container->get(AdapterInterface::class),
            $container->get(LazyQuahogClient::class)
        );
    }
}
