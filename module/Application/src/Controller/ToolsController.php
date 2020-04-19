<?php

declare(strict_types=1);

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;

/**
 * @\Application\Annotations\Protecc
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
