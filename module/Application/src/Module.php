<?php

namespace Application;

use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Mvc\MvcEvent;
use Zend\Navigation;
use Zend\Permissions\Acl\AclInterface;
use Zend\Session\Exception\RuntimeException as SessionValidationException;
use Zend\Session\SessionManager;
use Zend\View\Helper\Navigation as NavigationHelper;

class Module
{
    const VERSION = '2.2.2';

    public function onBootstrap(MvcEvent $e)
    {
        $application = $e->getApplication();
        $eventManager = $application->getEventManager();
        $serviceManager = $application->getServiceManager();
        $db = $serviceManager->get(AdapterInterface::class);

        $errorListener = new ErrorListener(
            $serviceManager->get('config'),
            $db
        );
        $errorListener->attach($eventManager);

        // Create a SessionManager so that it is ready for use as the default manager for all containers.
        $sessionManager = $serviceManager->get(SessionManager::class);
        try {
            // This will run the session validators so we can handle any issues centrally.
            $sessionManager->start();
        } catch (SessionValidationException $ex) {
            $sessionManager->destroy();
            $sessionManager->getValidatorChain()->clearListeners('session.validate');
        }

        // Set default route name for navigation helpers.
        Navigation\Page\Mvc::setDefaultRoute('default');

        // Determine the user's role.
        $groupResultSet = $db->query('SELECT id, groupname FROM scagroup', []);
        $groupList = [];
        foreach ($groupResultSet as $group) {
            $groupList[$group->id] = strtolower(str_replace(' ', '', $group->groupname));
        }

        $authService = $serviceManager->get(AuthenticationServiceInterface::class);
        $identity = $authService->getIdentity();
        $role = 'guest';
        if ($identity != null) {
            if ($identity == 'seneschal') {
                $role = 'admin';
            } elseif ($identity == 'servers') {
                $role = 'admin';
            } elseif (in_array($identity, $groupList)) {
                $role = 'seneschal';
            }
        }

        // Inject ACL into navigation helpers.
        NavigationHelper::setDefaultAcl($serviceManager->get(AclInterface::class));
        NavigationHelper::setDefaultRole($role);
    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
