<?php

namespace Application;

use Zend\Mvc\MvcEvent;

class Module
{
    const VERSION = '2.1.0';

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $errorListener = new ErrorListener($e->getApplication()->getServiceManager());
        $errorListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
