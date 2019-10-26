<?php

namespace User\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class Auth extends AbstractPlugin
{
    private $metadata;

    public function __invoke()
    {
        $this->metadata = $this->getController()->getRequest()->getMetadata('auth');
        return $this;
    }

    public function getId()
    {
        return $this->metadata['id'];
    }

    public function getLevel()
    {
        return $this->metadata['level'];
    }

    public function ensureLevel(array $permittedLevels)
    {
        if ($this->getLevel() == 'anyone') {
            // Not logged in - redirect to login page.
            return $this->getController()->redirect()->toRoute(
                'auth/login',
                [],
                ['query' => ['redirectUrl' => $this->getController()->currentUrl()]],
            );
        }
        if (!in_array($this->getLevel(), $permittedLevels)) {
            // Logged in but insufficient permissions - redirect to home page.
            return $this->getController()->redirect()->toRoute('home');
        }
    }
}
