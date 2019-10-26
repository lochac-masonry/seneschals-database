<?php

namespace Application\Controller;

class ToolsController extends BaseController
{
    public function indexAction()
    {
        return $this->redirect()->toRoute(null, ['action' => 'version'], [], true);
    }

    public function versionAction()
    {
        $this->layout()->title = 'Version';

        return [
            'appVersion'    => \Application\Module::VERSION,
            'googleVersion' => \Google_Client::LIBVER,
        ];
    }
}
