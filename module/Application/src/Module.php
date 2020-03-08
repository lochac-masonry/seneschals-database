<?php

namespace Application;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Mvc\MvcEvent;
use Zend\Session\Exception\RuntimeException as SessionValidationException;
use Zend\Session\SessionManager;

class Module
{
    public const VERSION = '2.4.0';

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
    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
