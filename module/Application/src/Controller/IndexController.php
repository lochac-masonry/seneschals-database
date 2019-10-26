<?php

namespace Application\Controller;

class IndexController extends DatabaseController
{
    public function indexAction()
    {
        $this->layout()->title = 'Lochac Seneschals\' Database';
        return [
            'authLevel' => $this->auth()->getLevel(),
        ];
    }
}
