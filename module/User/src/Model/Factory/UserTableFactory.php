<?php

namespace User\Model\Factory;

use Interop\Container\ContainerInterface;
use User\Model\{User, UserTable};
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\Factory\FactoryInterface;

class UserTableFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $db = $container->get(AdapterInterface::class);
        $resultSet = new ResultSet(ResultSet::TYPE_ARRAYOBJECT, new User());
        $tableGateway = new TableGateway('users', $db, null, $resultSet);
        return new UserTable($tableGateway);
    }
}
