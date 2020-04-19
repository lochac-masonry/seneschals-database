<?php

declare(strict_types=1);

namespace Application;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\{Insert, Sql};
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\{Application, MvcEvent};
use Laminas\Mvc\Controller\AbstractActionController;

class AccessFilter
{
    public function attach(SharedEventManagerInterface $events)
    {
        $events->attach(AbstractActionController::class, MvcEvent::EVENT_DISPATCH, [$this, 'onDispatch'], 100);
    }

    public function onDispatch(MvcEvent $e)
    {
        $controller = $e->getTarget();

        $reflectionClass = new ReflectionClass($controller);

        // Deprecated and will be removed in 2.0 but currently needed
        AnnotationRegistry::registerLoader('class_exists');
        $reader = new AnnotationReader();
        $annotations = $reader->getClassAnnotations($reflectionClass);

        print('<pre>' . var_export($annotations, true) . '</pre>');

        $protecc = false;
        if (!$protecc) {
            return;
        }

        // Not logged in - redirect to login page.
        return $controller->redirect()->toRoute(
            'auth/login',
            [],
            ['query' => ['redirectUrl' => $controller->currentUrl()]],
        );
    }
}
