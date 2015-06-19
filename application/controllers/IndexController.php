<?php

class IndexController extends SenDb_Controller
{
    public function indexAction()
    {
        $this->view->title = 'Lochac Seneschals\' Database';
    }

    public function homeAction()
    {
        $auth = authenticate();

        $this->view->title = 'Lochac Seneschals\' Database';

        $currentUser = 'You are currently logged in as ';
        if($auth['level'] == 'admin') {
            $currentUser .= "Kingdom Seneschal.<br />\n";
        } elseif($auth['level'] == 'user') {
            $currentUser .= "Group Seneschal.<br />\n";
        } else {
            $currentUser .= "no-one in particular. Did you get your password correct?<br />\n";
        }
        $this->addAlert($currentUser);

        $this->view->authlevel = $auth['level'];

    }

}

