<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\LazyQuahogClient;
use Interop\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\View\Renderer\PhpRenderer;

class EventControllerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        return new $requestedName(
            $container->get(AdapterInterface::class),
            $container->get(LazyQuahogClient::class),
            $container->get(PhpRenderer::class),
            $container->get('config')['google_calendar_id']
        );
    }
}
