<?php

class ToolsController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->_forward(version);
    }

    public function versionAction()
    {
        require_once('Zend/Version.php');

        $this->_helper->viewRenderer('echoMessage', null, true);
        $this->view->title = 'Zend Version';

        $this->view->message = Zend_Version::VERSION;
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
            throw new Exception('User not authorised for this task.');
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

            if(mail($mailTo, $mailSubj, $mailBody, $mailHead)) {
                $successCount++;
            } else {
                $this->view->message .= "<div class='bad'>Sending to seneschal of {$group->groupname} failed. "
                                      . "Try emailing manually.</div><br />\n";
            }

            $totalCount++;

        }

        $this->view->message .= "{$successCount} out of {$totalCount} emails sent successfully.<br />\n";
    }
    */

}
