<?php

use SenDb\Helper\Email;

class ErrorController extends \SenDb\Controller
{
    public function errorAction()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        global $config;

        $this->view->errors = $this->_getParam('error_handler');

                                                            //----------------------------------------------------------
                                                            // Attempt to insert to error log.
                                                            // Exception may have been a database connection problem,
                                                            // so fall back to email if necessary.
                                                            //----------------------------------------------------------
        $exception = $this->view->errors->exception;

        try {
            $db->insert(
                'errorLog',
                array(
                    'errorDateTime'  => date('Y-m-d H:i:s'),
                    'exceptionClass' => get_class($exception),
                    'message'        => substr($exception->getMessage(), 0, 512)
                )
            );
        } catch (Exception $e) {
            if (isset($config->exception->email)) {
                $mailSubj = 'Lochac Seneschals\' Database: Unhandled Exception';

                $mailBody = "An error occurred in the error handler - something is probably going wrong.\n\n"
                          . "New exception:\n" . $e->getMessage() . "\n\n"
                          . "Original exception:\n" . print_r($exception, true);

                $mailHead = "From: information@lochac.sca.org";

                Email::send($config->exception->email, $mailSubj, $mailBody, $mailHead);
            }
        }
    }
}
