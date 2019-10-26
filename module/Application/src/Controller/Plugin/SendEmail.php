<?php

namespace Application\Controller\Plugin;

use InvalidArgumentException;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

class SendEmail extends AbstractPlugin
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function __invoke($to, $subject, $body, $header = null)
    {
        if (is_array($to)) {
            $to = implode(', ', $to);
        }
        if (is_array($header)) {
            $header = implode("\r\n", $header);
        }

        if (!is_string($to)) {
            throw new InvalidArgumentException('Argument $to must be string or array of strings');
        }
        if (!is_string($subject)) {
            throw new InvalidArgumentException('Argument $subject must be string');
        }
        if (!is_string($body)) {
            throw new InvalidArgumentException('Argument $body must be string');
        }
        if (isset($header) && !is_string($header)) {
            throw new InvalidArgumentException('Argument $header must be string or array of strings');
        }

        $header .= "\r\nContent-Type: text/plain;charset=utf-8";

                                                            //----------------------------------------------------------
                                                            // Redirect all email to the debug address if it is set
                                                            //----------------------------------------------------------
        if (isset($this->config['debugEmail'])) {
            $to = $this->config['debugEmail'];
        }

        return mail($to, $subject, $body, $header);
    }
}
