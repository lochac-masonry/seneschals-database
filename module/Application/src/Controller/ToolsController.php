<?php

namespace Application\Controller;

class ToolsController extends BaseController
{
    public function indexAction()
    {
        return $this->forwardToAction('version');
    }

    public function versionAction()
    {
        $this->layout()->title = 'Version';

        return [
            'appVersion'    => \Application\Module::VERSION,
            'zendVersion'   => \Zend\Version\Version::VERSION,
            'googleVersion' => \Google_Client::LIBVER,
        ];
    }
}
