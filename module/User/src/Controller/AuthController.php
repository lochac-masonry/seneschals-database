<?php

declare(strict_types=1);

namespace User\Controller;

use Firebase\JWT\{JWT, Key};
use Laminas\Authentication\AuthenticationServiceInterface;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Session\ManagerInterface;
use Laminas\Uri\Uri;
use User\Form;

class AuthController extends AbstractActionController
{
    /** @var AuthenticationServiceInterface */
    private $authService;
    /** @var ManagerInterface */
    private $sessionManager;
    private $ssoConfig;

    public function __construct(
        AuthenticationServiceInterface $authService,
        ManagerInterface $sessionManager,
        $ssoConfig
    ) {
        $this->authService = $authService;
        $this->sessionManager = $sessionManager;
        $this->ssoConfig = $ssoConfig;
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
            return $this->handleSuccess($values['redirectUrl']);
        }

        return $viewModel;
    }

    public function logoutAction()
    {
        $this->authService->clearIdentity();
        $this->sessionManager->destroy();
        return $this->redirect()->toRoute('home');
    }

    public function singleSignOnAction()
    {
        // First, log out of any existing session.
        $this->authService->clearIdentity();

        // Check the ID token is present and valid.
        $request = $this->getRequest();
        $redirectUrl = isset($request->getQuery()['redirectUrl']) ? $request->getQuery()['redirectUrl'] : '';
        if (!isset($request->getQuery()['id_token'])) {
            throw new \UnexpectedValueException('Token not provided');
        }
        $idToken = $request->getQuery()['id_token'];

        $payload = JWT::decode($idToken, new Key($this->ssoConfig['key'], $this->ssoConfig['algorithm']));
        if (!isset($payload->iat) || !isset($payload->exp)) {
            throw new \UnexpectedValueException('Token issue or expiry time not set');
        }
        if (!isset($payload->iss) || $payload->iss !== $this->ssoConfig['issuer']) {
            throw new \UnexpectedValueException('Token issuer wrong or missing');
        }
        if (!isset($payload->aud) || $payload->aud !== $this->ssoConfig['audience']) {
            throw new \UnexpectedValueException('Token audience wrong or missing');
        }
        if (!isset($payload->sub)) {
            throw new \UnexpectedValueException('Token subject wrong or missing');
        }

        // Use the subject of the ID token as the username.
        $this->authService->getStorage()->write($payload->sub);
        return $this->handleSuccess($redirectUrl);
    }

    private function handleSuccess(string $redirectUrl)
    {
        $this->sessionManager->regenerateId();

        // Ensure redirect URL is valid and relative, i.e. not hijacking the user to a different site.
        $uri = new Uri($redirectUrl);
        if (!$uri->isValid() || $uri->getHost() != null) {
            $redirectUrl = '';
        }

        // If the redirect URL is not given or is not valid, redirect to the home page.
        if (empty($redirectUrl)) {
            return $this->redirect()->toRoute('home');
        }
        // Otherwise, redirect to that URL.
        return $this->redirect()->toUrl($redirectUrl);
    }
}
