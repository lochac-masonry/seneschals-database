<?php

declare(strict_types=1);

namespace Application;

use Socket\Raw\Factory;
use Xenolope\Quahog\Client;

class LazyQuahogClient
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        $socket = (new Factory())->createClient($this->config['clamd_socket']);
        $quahog = new Client($socket, mode: PHP_NORMAL_READ);
        return $quahog;
    }
}
