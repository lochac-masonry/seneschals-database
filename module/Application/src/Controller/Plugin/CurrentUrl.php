<?php

declare(strict_types=1);

namespace Application\Controller\Plugin;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

class CurrentUrl extends AbstractPlugin
{
    public function __invoke()
    {
        return $this->getController()
            ->getRequest()
            ->getUri()
            ->setScheme(null)
            ->setHost(null)
            ->setPort(null)
            ->setUserInfo(null)
            ->toString();
    }
}
