<?php

declare(strict_types=1);

namespace User\Controller\Factory;

use Interop\Container\ContainerInterface;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\Session\SessionManager;
use User\SsoConfig;

class AuthControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $authService = $container->get(AuthenticationServiceInterface::class);
        $sessionManager = $container->get(SessionManager::class);
        $ssoConfig = new SsoConfig($container->get('config')['sso']);
        return new $requestedName($authService, $sessionManager, $ssoConfig);
    }
}
