<?php

namespace Application\Controller\Plugin;

use InvalidArgumentException;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

class Alert extends AbstractPlugin
{
    public function __invoke($message = null)
    {
        if ($message === null) {
            return $this;
        }
        $this->addAlert($message);
    }

    public function good($message)
    {
        $this->addAlert($message, 'good');
    }

    public function bad($message)
    {
        $this->addAlert($message, 'bad');
    }

    private function addAlert($message, $type = null)
    {
        if (!is_string($message)) {
            throw new InvalidArgumentException('Argument $message must be string');
        }

        $layout = $this->getController()->layout();
        $alerts = isset($layout->alerts) ? $layout->alerts : [];
        $alerts[] = isset($type) ? ['message' => $message, 'type' => $type] : $message;
        $layout->alerts = $alerts;
    }
}
