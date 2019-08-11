<?php

namespace SenDb;

use InvalidArgumentException;

class Controller extends \Zend_Controller_Action
{
    const ALERT_GOOD = 'good';
    const ALERT_BAD  = 'bad';

    protected function addAlert($message, $type = null)
    {
        if (!is_string($message)) {
            throw new InvalidArgumentException('Argument $message must be string');
        }
        if (isset($type)
          && $type !== self::ALERT_GOOD
          && $type !== self::ALERT_BAD) {
            throw new InvalidArgumentException('Argument $type must be unspecified or one of ALERT_GOOD or ALERT_BAD');
        }

        if (!isset($this->view->alerts)) {
            $this->view->alerts = array();
        }

        $this->view->alerts[] = isset($type) ? array('message' => $message, 'type' => $type) : $message;
    }

    protected function clearAlerts()
    {
        $this->view->alerts = array();
    }
}
