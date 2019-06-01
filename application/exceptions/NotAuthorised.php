<?php

class SenDb_Exception_NotAuthorised extends Exception
{
    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        if (!isset($message)) {
            $message = 'User is not authorised to perform that action.';
        }

        parent::__construct($message, $code, $previous);
    }
}
