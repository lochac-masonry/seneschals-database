<?php

declare(strict_types=1);

namespace User;

use User\Annotations\EnsureRole;
use Doctrine\Common\Annotations\AnnotationReader;
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\Mvc\Controller\AbstractActionController;

class AccessFilter
{
    /** @var string */
    private $role;

    public function __construct(string $role)
    {
        $this->role = $role;
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

        $reader = new AnnotationReader();
        $ensureRoleAnnotation = $reader->getMethodAnnotation($reflectionMethod, EnsureRole::class)
            ?? $reader->getClassAnnotation($reflectionClass, EnsureRole::class);

        if (!$ensureRoleAnnotation) {
            // Auth not required.
            return;
        }

        if (in_array($this->role, $ensureRoleAnnotation->permittedRoles)) {
            // Auth required and user has the correct role.
            return;
        }

        if ($this->role === 'guest') {
            // Not logged in - redirect to login page.
            return $controller->redirect()->toRoute(
                'auth/login',
                [],
                ['query' => ['redirectUrl' => $controller->currentUrl()]],
            );
        }

        // Logged in but insufficient permissions - redirect to home page.
        return $controller->redirect()->toRoute('home');
    }
}
