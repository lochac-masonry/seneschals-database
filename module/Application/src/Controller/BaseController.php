<?php

namespace Application\Controller;

use InvalidArgumentException;
use Zend\Authentication\AuthenticationServiceInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;

class BaseController extends AbstractActionController
{
    const ALERT_GOOD = 'good';
    const ALERT_BAD  = 'bad';

    protected $auth;
    protected $authService;
    protected $config;
    protected $db;

    public function __construct(
        AuthenticationServiceInterface $authService,
        array $config,
        AdapterInterface $db
    ) {
        $this->authService = $authService;
        $this->config = $config;
        $this->db = $db;
    }

    private function prepareAuthMetadata()
    {
        // Prepare list of known users based on groups.
        $groupResultSet = $this->db->query('SELECT id, groupname FROM scagroup', []);
        $groupList = [];
        foreach ($groupResultSet as $group) {
            $groupList[$group->id] = strtolower(str_replace(' ', '', $group->groupname));
        }

        // Determine auth metadata based on the logged-in identity.
        $auth = ['id' => null, 'level' => 'anyone'];
        $identity = $this->authService->getIdentity();
        if ($identity != null) {
            if ($identity == 'seneschal') {
                $auth = ['id' => 1, 'level' => 'admin'];
            } elseif ($identity == 'servers') {
                $auth = ['id' => 1, 'level' => 'admin'];
            } elseif (in_array($identity, $groupList)) {
                $auth = ['id' => array_search($identity, $groupList), 'level' => 'user'];
            }
        }

        // Store auth info ready for access by the controller or layout.
        $this->auth = $auth;
    }

    public function onDispatch(MvcEvent $e)
    {
        $this->prepareAuthMetadata();
        return parent::onDispatch($e);
    }

    protected function getCurrentUrl()
    {
        return $this->getRequest()->getUri()
            ->setScheme(null)
            ->setHost(null)
            ->setPort(null)
            ->setUserInfo(null)
            ->toString();
    }

    protected function ensureAuthLevel(array $permittedLevels)
    {
        if ($this->auth['level'] == 'anyone') {
            // Not logged in - redirect to login page.
            return $this->redirect()->toRoute(
                null,
                ['controller' => 'auth', 'action' => 'login'],
                ['query' => ['redirectUrl' => $this->getCurrentUrl()]],
            );
        }
        if (!in_array($this->auth['level'], $permittedLevels)) {
            // Logged in but insufficient permissions - redirect to home page.
            return $this->redirect()->toRoute();
        }
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
