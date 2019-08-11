<?php

namespace SenDb\Helper;

use IllegalArgumentException;

class Email
{
    public static function send($to, $subject, $body, $header = null)
    {
        global $config;

        if (is_array($to)) {
            $to = implode(', ', $to);
        }
        if (is_array($header)) {
            $header = implode("\r\n", $header);
        }

        if (!is_string($to)) {
            throw new IllegalArgumentException('Argument $to must be string or array of strings');
        }
        if (!is_string($subject)) {
            throw new IllegalArgumentException('Argument $subject must be string');
        }
        if (!is_string($body)) {
            throw new IllegalArgumentException('Argument $body must be string');
        }
        if (isset($header)
          && !is_string($header)) {
            throw new IllegalArgumentException('Argument $header must be string or array of strings');
        }

        $header .= "\r\nContent-Type: text/plain;charset=utf-8";

                                                            //----------------------------------------------------------
                                                            // Redirect all email to the debug address if it is set
                                                            //----------------------------------------------------------
        if (isset($config->debug->email)) {
            $to = $config->debug->email;
        }

        return mail($to, $subject, $body, $header);
    }
}
