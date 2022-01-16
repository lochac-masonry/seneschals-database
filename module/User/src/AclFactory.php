<?php

declare(strict_types=1);

namespace User;

use Interop\Container\ContainerInterface;
use Laminas\Permissions\Acl\Acl;
use Laminas\ServiceManager\Factory\FactoryInterface;

class AclFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $acl = new Acl();

        $acl->addResource('auth');
        $acl->addResource('event');
        $acl->addResource('group');
        $acl->addResource('postcode');
        $acl->addResource('report');
        $acl->addResource('tools');

        $acl->addRole('guest');
        $acl->allow('guest', 'event', 'create');
        $acl->allow('guest', 'postcode', 'list');

        $acl->addRole('seneschal', ['guest']);
        $acl->allow('seneschal', 'auth', 'logout');
        $acl->allow('seneschal', 'event', ['edit', 'list']);
        $acl->allow('seneschal', 'group', 'manage_aliases');
        $acl->allow('seneschal', 'report', 'submit');

        $acl->addRole('admin');
        $acl->allow('admin');

        return $acl;
    }
}
