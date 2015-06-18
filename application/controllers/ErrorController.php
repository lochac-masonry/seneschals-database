<?php

class ErrorController extends SenDb_Controller
{
    public function errorAction()
    {
        $this->view->errors = $this->_getParam('error_handler');
    }

}

