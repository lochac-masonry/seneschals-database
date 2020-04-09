<?php

declare(strict_types=1);

namespace Application\Controller;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Mvc\Controller\AbstractActionController;

class DatabaseController extends AbstractActionController
{
    protected $db;

    public function __construct(AdapterInterface $db)
    {
        $this->db = $db;
    }
}
