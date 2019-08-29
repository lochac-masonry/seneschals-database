<?php

namespace Application\Controller;

use InvalidArgumentException;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Mvc\Controller\AbstractActionController;

class BaseController extends AbstractActionController
{
    const ALERT_GOOD = 'good';
    const ALERT_BAD  = 'bad';

    protected $config;
    protected $db;

    public function __construct(array $config, AdapterInterface $db)
    {
        $this->config = $config;
        $this->db = $db;
    }

    protected function addAlert($message, $type = null)
    {
        if (!is_string($message)) {
            throw new InvalidArgumentException('Argument $message must be string');
        }
        if (isset($type) && $type !== self::ALERT_GOOD && $type !== self::ALERT_BAD) {
            throw new InvalidArgumentException('Argument $type must be unspecified or one of ALERT_GOOD or ALERT_BAD');
        }

        $layout = $this->layout();
        $alerts = isset($layout->alerts) ? $layout->alerts : [];
        $alerts[] = isset($type) ? ['message' => $message, 'type' => $type] : $message;
        $layout->alerts = $alerts;
    }

    protected function clearAlerts()
    {
        $this->layout()->alerts = [];
    }

    protected function arrayIndex($array, $keyField, $valueField)
    {
        $result = [];
        foreach ($array as $item) {
            $result[$item[$keyField]] = $item[$valueField];
        }
        return $result;
    }

    protected function getDb()
    {
        return $this->db;
    }

    protected function authenticate()
    {
        global $authLevel;
        $config = $this->config['auth'];
        $db = $this->getDb();

        $groupResultSet = $db->query('SELECT id, groupname FROM scagroup', []);
        $groupList = [];
        foreach ($groupResultSet as $group) {
            $groupList[$group->id] = strtolower(str_replace(' ', '', $group->groupname));
        }

        $username = isset($_SERVER['PHP_AUTH_USER']) ? $_SERVER['PHP_AUTH_USER'] : null;
        $passhash = isset($_SERVER['PHP_AUTH_PW']) ? hash('sha256', $_SERVER['PHP_AUTH_PW']) : null;

        if (isset($_GET['bypass']) && $_GET['bypass'] == 'true') {
            $auth['level'] = 'anyone';
        } elseif ($username == $config['admin']['username'] && $passhash == $config['admin']['passhash']) {
            $auth['level'] = 'admin';
            $auth['id'] = 1;
        } elseif ($username == $config['wheel']['username'] && $passhash == $config['wheel']['passhash']) {
            $auth['level'] = 'admin';
            $auth['id'] = 1;
        } elseif (in_array($username, $groupList) && $passhash == $config['user']['passhash']) {
            $auth['level'] = 'user';
            $auth['id'] = array_search($_SERVER['PHP_AUTH_USER'], $groupList);
        } elseif ($username == 'guest') {
            $auth['level'] = 'anyone';
        } else {
            $this->response->setStatusCode(401);
            Header('WWW-Authenticate: Basic realm="Seneschals\' Database"');
            return false;
        }

        $authLevel = $auth['level'];
        return $auth;
    }

    protected function forwardToAction($action)
    {
        $this->getEvent()->getRouteMatch()->setParam('action', $action);
        $method = static::getMethodFromAction($action);

        if (!method_exists($this, $method)) {
            $method = 'notFoundAction';
        }

        return $this->$method();
    }

    protected function sendEmail($to, $subject, $body, $header = null)
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
