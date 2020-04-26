<?php

declare(strict_types=1);

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use User\Annotations\EnsureRole;

/**
 * @EnsureRole
 */
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
