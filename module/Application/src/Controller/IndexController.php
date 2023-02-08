<?php

declare(strict_types=1);

namespace Application\Controller;

use Laminas\View\Model\ViewModel;

class IndexController extends DatabaseController
{
    public function indexAction()
    {
        $view = new ViewModel([
            'foo' => 'bar',
        ]);
        $view->setTemplate('event/email/announce.phtml');
        $view->setTerminal(true);
        $serviceManager = $this->getServiceLocator();
        echo($serviceManager->get('ViewRenderer')->render($view));

        $this->layout()->title = 'Lochac Seneschals\' Database';
        return [
            'authLevel' => $this->auth()->getLevel(),
        ];
    }
}
