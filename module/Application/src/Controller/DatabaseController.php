<?php

namespace Application\Controller;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Mvc\Controller\AbstractActionController;

class DatabaseController extends AbstractActionController
{
    protected $db;

    public function __construct(AdapterInterface $db)
    {
        $this->db = $db;
    }
}
