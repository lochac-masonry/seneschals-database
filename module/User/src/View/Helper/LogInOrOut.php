<?php

namespace User\View\Helper;

use Zend\Authentication\AuthenticationServiceInterface;
use Zend\View\Helper\AbstractHelper;

class LogInOrOut extends AbstractHelper
{
    private $authService;

    public function __construct(AuthenticationServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function __invoke()
    {
        $isLoggedIn = $this->authService->hasIdentity();
        $route = $isLoggedIn ? 'auth/logout' : 'auth/login';
        $url = $this->view->url($route);
        $label = $isLoggedIn ? 'Log Out' : 'Log In';
        return sprintf('<a href="%s">%s</a>', $url, $label);
    }
}
