<?php

namespace User\Controller;

use User\Model\UserTable;
use Zend\Mvc\Controller\AbstractActionController;

class UserController extends AbstractActionController
{
    private $userTable;

    public function __construct(UserTable $userTable)
    {
        $this->userTable = $userTable;
    }

    public function indexAction()
    {
        return [
            'users' => $this->userTable->fetchAll(),
        ];
    }
}
