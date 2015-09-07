<?php

class ToolsController extends SenDb_Controller
{
    public function indexAction()
    {
        $this->_forward('version');
    }

    public function versionAction()
    {
        $this->_helper->viewRenderer('echoMessage', null, true);
        $this->view->title = 'Version';

        require_once('Google/autoload.php');

        $this->view->message = 'Lochac Seneschals\' Database: ' . SENDB_VERSION . "<br />\n"
                             . 'Zend Framework: ' . Zend_Version::VERSION . "<br />\n"
                             . 'Google API PHP Client: ' . Google_Client::LIBVER;
    }

    /*
     * To use this function, uncomment and replace PASSWORD in the email text with the actual password.
     */
    /*
    private function passwordReminderAction()
    {
        $auth = authenticate();
        global $db;
        if($auth['level'] != 'admin') {
            throw new SenDb_Exception_NotAuthorised();
            return;
        }

        $this->_helper->viewRenderer('echoMessage', null, true);
        $this->view->title = 'Send Password Reminders';

        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        $groups = $db->fetchAll("SELECT groupname, email, scaname, type FROM scagroup");

        foreach($groups as $group) {
            $mailTo = $group->email;

            $mailSubj = "Lochac Seneschals' Database Password Reminder";

            $mailBody = "Greetings {$group->scaname}!\n\n" .
                        "This message is being sent to you because you are listed as the seneschal of the " .
                        "{$group->type} of {$group->groupname}. If this is not the case, please delete this message.\n\n" .
                        "To access the Lochac Seneschals' Database for quarterly reporting, editing email aliases or " .
                        "updating the details of your Baron and Baroness, please go to http://lochac.sca.org" .
                        Zend_Layout::getMvcInstance()->relativeUrl . " and enter the username and password listed below.\n\n" .
                        "Username: " . strtolower(str_replace(' ','',$group->groupname)) . "\n" .
                        "Password: PASSWORD\n\n" .
                        "Kind regards,\n" .
                        "The Lochac Seneschals' Database";

            $mailHead = "From:{$group->email}";

            if(SenDb_Helper_Email::send($mailTo, $mailSubj, $mailBody, $mailHead)) {
                $successCount++;
            } else {
                $this->addAlert('Sending to seneschal of ' . $group->groupname . ' failed. Try emailing manually.', SenDb_Controller::ALERT_BAD);
            }

            $totalCount++;

        }

        $this->addAlert($successCount . ' out of ' . $totalCount . ' emails sent successfully.');
    }
    */

}
