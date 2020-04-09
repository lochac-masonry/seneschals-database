<?php

declare(strict_types=1);

namespace User;

use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\Permissions\Acl\AclInterface;
use Laminas\View\Helper\Navigation as NavigationHelper;

class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $application = $e->getApplication();
        $serviceManager = $application->getServiceManager();
        $db = $serviceManager->get(AdapterInterface::class);

        // Determine the user's identity and role.
        $groupResultSet = $db->query('SELECT id, groupname FROM scagroup', []);
        $groupList = [];
        foreach ($groupResultSet as $group) {
            $groupList[$group->id] = strtolower(str_replace(' ', '', $group->groupname));
        }

        $authService = $serviceManager->get(AuthenticationServiceInterface::class);
        $identity = $authService->getIdentity();
        $auth = ['id' => null, 'level' => 'anyone'];
        $role = 'guest';
        if ($identity != null) {
            if ($identity == 'seneschal') {
                $auth = ['id' => 1, 'level' => 'admin'];
                $role = 'admin';
            } elseif ($identity == 'servers') {
                $auth = ['id' => 1, 'level' => 'admin'];
                $role = 'admin';
            } elseif (in_array($identity, $groupList)) {
                $auth = ['id' => array_search($identity, $groupList), 'level' => 'user'];
                $role = 'seneschal';
            }
        }

        // Auth metadata into request for later use.
        $e->getRequest()->setMetadata('auth', $auth);

        // Inject ACL and role into navigation helpers.
        NavigationHelper::setDefaultAcl($serviceManager->get(AclInterface::class));
        NavigationHelper::setDefaultRole($role);
    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
