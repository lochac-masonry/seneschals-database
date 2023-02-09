<?php

declare(strict_types=1);

namespace Application\Controller\Plugin;

use InvalidArgumentException;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

class SendEmail extends AbstractPlugin
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function __invoke($to, $subject, $body, $onBehalfOf = null, $html = false)
    {
        if (is_array($to)) {
            $to = implode(', ', $to);
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

        if ($onBehalfOf) {
            $header = "From: {$onBehalfOf}\r\nSender: {$this->config['fromEmail']}";
        } else {
            $header = "From: {$this->config['fromEmail']}";
        }

        $contentType = $html ? 'text/html' : 'text/plain';
        $header .= "\r\nContent-Type: {$contentType};charset=utf-8";

                                                            //----------------------------------------------------------
                                                            // Redirect all email to the debug address if it is set
                                                            //----------------------------------------------------------
        if (isset($this->config['debugEmail'])) {
            $to = $this->config['debugEmail'];
        }

        return mail($to, $subject, $body, $header);
    }
}
