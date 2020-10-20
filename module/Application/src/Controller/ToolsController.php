<?php

declare(strict_types=1);

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;

class ToolsController extends AbstractActionController
{
    public function versionAction()
    {
        $this->layout()->title = 'Version';

        return [
            'appVersion'    => \Application\Module::VERSION,
            'googleVersion' => \Google_Client::LIBVER,
        ];
    }

    public function keepAliveAction()
    {
        if (in_array($this->getRequest()->getMetadata('auth')['level'], ['admin', 'user'])) {
            // Login session has not timed out.
            return '';
        }
        $response = $this->getResponse();
        $response->setStatusCode(401);
        return $response;
    }
}
