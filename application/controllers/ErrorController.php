<?php

class ErrorController extends Zend_Controller_Action
{
    public function errorAction()
    {
        $this->view->errors = $this->_getParam('error_handler');
    }

}

