<?php

declare(strict_types=1);

namespace User;

use User\Annotations\Protecc;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Controller\AbstractActionController;

class AccessFilter
{
    /** @var AuthenticationServiceInterface */
    private $authService;

    public function __construct(AuthenticationServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function attach(SharedEventManagerInterface $events)
    {
        $events->attach(AbstractActionController::class, MvcEvent::EVENT_DISPATCH, [$this, 'onDispatch'], 100);
    }

    public function onDispatch(MvcEvent $e)
    {
        $controller = $e->getTarget();
        $routeMatch = $e->getRouteMatch();
        $action = $routeMatch->getParam('action', 'not-found');
        $actionMethod = $controller::getMethodFromAction($action);
        if (!method_exists($controller, $actionMethod)) {
            $actionMethod = 'notFoundAction';
        }

        $reflectionClass = new \ReflectionClass($controller);
        $reflectionMethod = $reflectionClass->getMethod($actionMethod);

        AnnotationRegistry::registerLoader('class_exists');
        $reader = new AnnotationReader();
        $protecc = $reader->getMethodAnnotation($reflectionMethod, Protecc::class)
            || $reader->getClassAnnotation($reflectionClass, Protecc::class);

        if ($protecc && !$this->authService->hasIdentity()) {
            // Not logged in - redirect to login page.
            return $controller->redirect()->toRoute(
                'auth/login',
                [],
                ['query' => ['redirectUrl' => $controller->currentUrl()]],
            );
        }
    }
}
