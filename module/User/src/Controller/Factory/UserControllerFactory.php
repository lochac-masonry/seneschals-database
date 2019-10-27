<?php

namespace User\Controller\Factory;

use Interop\Container\ContainerInterface;
use User\Model\UserTable;
use Zend\ServiceManager\Factory\FactoryInterface;

class UserControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $userTable = $container->get(UserTable::class);
        return new $requestedName($userTable);
    }
}
