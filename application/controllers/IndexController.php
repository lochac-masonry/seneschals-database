<?php

class IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        
    }

    public function homeAction()
    {
        $auth = authenticate();
        
        $this->view->title = 'Lochac Seneschals\' Database';
        $this->view->message = 'You are currently logged in as ';
        if($auth['level'] == 'admin') $this->view->message .= "Kingdom Seneschal.<br />\n";
        elseif($auth['level'] == 'user') $this->view->message .= "Group Seneschal.<br />\n";
        else $this->view->message .= "no-one in particular. Did you get your password correct?<br />\n";
        
        $this->view->authlevel = $auth['level'];
    }

}

