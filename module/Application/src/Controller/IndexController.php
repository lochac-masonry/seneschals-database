<?php

namespace Application\Controller;

class IndexController extends BaseController
{
    public function indexAction()
    {
        $this->layout()->title = 'Lochac Seneschals\' Database';
        return [
            'authLevel' => $this->auth['level'],
        ];
    }
}
