<?php

namespace Application;

use Zend\Mvc\MvcEvent;
use Zend\Session\Exception\RuntimeException as SessionValidationException;
use Zend\Session\SessionManager;

class Module
{
    const VERSION = '2.2.1';

    public function onBootstrap(MvcEvent $e)
    {
        $application = $e->getApplication();
        $eventManager = $application->getEventManager();
        $serviceManager = $application->getServiceManager();

        $errorListener = new ErrorListener(
            $serviceManager->get('config'),
            $serviceManager->get('Zend\Db\Adapter\AdapterInterface')
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
    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
