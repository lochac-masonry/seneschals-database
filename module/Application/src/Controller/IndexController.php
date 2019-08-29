<?php

namespace Application\Controller;

class IndexController extends BaseController
{
    public function indexAction()
    {
        $this->layout()->title = 'Lochac Seneschals\' Database';
    }

    public function homeAction()
    {
        $auth = $this->authenticate();
        // In this case treat in-progress authentication as level=anyone.
        if (!$auth) {
            $auth = ['level' => 'anyone'];
        }

        $this->layout()->title = 'Lochac Seneschals\' Database';

        $currentUser = 'You are currently logged in as ';
        if ($auth['level'] == 'admin') {
            $currentUser .= "Kingdom Seneschal.<br />\n";
        } elseif ($auth['level'] == 'user') {
            $currentUser .= "Group Seneschal.<br />\n";
        } else {
            $currentUser .= "no-one in particular. Did you get your password correct?<br />\n";
        }
        $this->addAlert($currentUser);
    }
}
