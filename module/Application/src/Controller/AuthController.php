<?php

namespace Application\Controller;

use Application\Form;
use Zend\Uri\Uri;

class AuthController extends BaseController
{
    public function indexAction()
    {
        return $this->forwardToAction('login');
    }

    public function loginAction()
    {
        $request = $this->getRequest();
        $redirectUrl = isset($request->getQuery()['redirectUrl']) ? $request->getQuery()['redirectUrl'] : '';
        $loginForm = new Form\Login($redirectUrl);
        $viewModel = [
            'loginForm' => $loginForm,
            'messages'  => [],
        ];

        if (!$request->isPost()) {
            return $viewModel;
        }

        $loginForm->setData($request->getPost());
        if (!$loginForm->isValid()) {
            return $viewModel;
        }

        $values = $loginForm->getData();
        $this->authService->getAdapter()
            ->setIdentity($values['username'])
            ->setCredential($values['password']);
        $result = $this->authService->authenticate();
        $viewModel['messages'] = $result->getMessages();

        if ($result->isValid()) {
            // Ensure redirect URL is valid and relative, i.e. not hijacking the user to a different site.
            $redirectUrl = $values['redirectUrl'];
            $uri = new Uri($redirectUrl);
            if (!$uri->isValid() || $uri->getHost() != null) {
                $redirectUrl = '';
            }

            // If the redirect URL is not given or is not valid, redirect to the home page.
            if (empty($redirectUrl)) {
                return $this->redirect()->toRoute();
            }
            // Otherwise, redirect to that URL.
            return $this->redirect()->toUrl($redirectUrl);
        }

        return $viewModel;
    }

    public function logoutAction()
    {
        $this->authService->clearIdentity();
        return $this->redirect()->toRoute();
    }
}
