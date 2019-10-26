<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;

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
}
