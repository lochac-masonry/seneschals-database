<?php

namespace Application;

use Zend\Mvc\MvcEvent;

class Module
{
    const VERSION = '2.1.0';

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
    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
