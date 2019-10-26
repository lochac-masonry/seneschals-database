<?php

namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;

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
