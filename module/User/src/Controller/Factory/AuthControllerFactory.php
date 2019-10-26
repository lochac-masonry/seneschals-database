<?php

namespace User\Controller\Factory;

use Interop\Container\ContainerInterface;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\ServiceManager\Factory\FactoryInterface;

class AuthControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $authService = $container->get(AuthenticationServiceInterface::class);
        return new $requestedName($authService);
    }
}
