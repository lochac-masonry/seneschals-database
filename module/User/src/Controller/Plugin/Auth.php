<?php

declare(strict_types=1);

namespace User\Controller\Plugin;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

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
}
