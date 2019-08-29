<?php

namespace Application;

use Zend\Db\Sql\{Insert, Sql};
use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\{Application, MvcEvent};
use Zend\ServiceManager\ServiceLocatorInterface;

class ErrorListener
{
    protected $services;

    public function __construct(ServiceLocatorInterface $services)
    {
        $this->services = $services;
    }

    public function attach(EventManagerInterface $events)
    {
        $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'onError']);
        $events->attach(MvcEvent::EVENT_RENDER_ERROR, [$this, 'onError']);
    }

    public function onError(MvcEvent $e)
    {
        // Some logic copied from Zend\Mvc\View\Http\ExceptionStrategy::prepareExceptionViewModel
        // Do nothing if no error in the event
        $error = $e->getError();
        if (empty($error)) {
            return;
        }

        switch ($error) {
            case Application::ERROR_CONTROLLER_NOT_FOUND:
            case Application::ERROR_CONTROLLER_INVALID:
            case Application::ERROR_ROUTER_NO_MATCH:
                // Specifically not handling these
                return;

            case Application::ERROR_EXCEPTION:
            default:
                $exception = $e->getParam('exception');
                $this->logExceptionToDatabase($exception);
                $this->sendExceptionEmail($exception);
                break;
        }
    }

    private function logExceptionToDatabase($exception)
    {
        $db = $this->services->get('Zend\Db\Adapter\Adapter');
        try {
            $db->query(
                (new Sql($db))->buildSqlString(
                    (new Insert('errorLog'))
                        ->values([
                            'errorDateTime'  => date('Y-m-d H:i:s'),
                            'exceptionClass' => get_class($exception),
                            'message'        => substr($exception->getMessage(), 0, 512),
                        ])
                ),
                $db::QUERY_MODE_EXECUTE
            );
        } catch (\Throwable $ex) {
            // Nothing more we can do.
        }
    }

    private function sendExceptionEmail($exception)
    {
        $config = $this->services->get('config');
        if (!isset($config['exceptionEmail'])) {
            return;
        }

        $mailSubj = 'Lochac Seneschals\' Database: Unhandled Exception';

        $mailBody = '';
        $count = 0;
        do {
            $mailBody .= get_class($exception) . "\n"
                      . 'File: ' . $exception->getFile() . ':' . $exception->getLine() . "\n"
                      . 'Message: ' . $exception->getMessage() . "\n"
                      . 'Stack trace: ' . $exception->getTraceAsString() . "\n\n";
            $exception = $exception->getPrevious();
            $count++;
        } while ($exception && $count < 10);

        $mailHead = "From: information@lochac.sca.org\r\nContent-Type: text/plain;charset=utf-8";

        mail($config['exceptionEmail'], $mailSubj, $mailBody, $mailHead);
    }
}
