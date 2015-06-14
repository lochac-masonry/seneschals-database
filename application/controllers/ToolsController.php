<?php

class ToolsController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->_forward(version);
    }

    public function formtestAction()
    {
        $this->_helper->viewRenderer('echoMessage',null,true);
        $this->view->title = 'Autoloaded Form';

        $form = new SenDb_Form_Test();

        $this->view->message = $form->doIt();
    }

    public function gcalAction()
    {
        $this->_helper->viewRenderer('echoMessage',null,true);
        $this->view->title = 'GCal Test';
        
        require_once('Google/autoload.php');

        $serviceAccount = json_decode(file_get_contents('google-key.json'));

        $credentials = new Google_Auth_AssertionCredentials(
            $serviceAccount->client_email,
            Google_Service_Calendar::CALENDAR,
            $serviceAccount->private_key
        );

        $client = new Google_Client();
        $client->setAssertionCredentials($credentials);

        if ($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion();
        }

        $service = new Google_Service_Calendar($client);

        $calendarId = 'dylan-kerr@users.noreply.github.com';
        $eventId = '9f2av6jbvcb7b4gom8mii0m0os';

        try {
            $this->view->message = '<pre>' . print_r($service->events->get($calendarId, $eventId), true) . '</pre>';
        } catch (Exception $e) {
            $this->view->message = '<pre>' . print_r($e, true) . '</pre>';
        }

        //$event = $service->events->get($calendarId, $eventId);

        //$event->summary = 'UpdatedEvent';

        //$event = $service->events->update($calendarId, $eventId, $event);

        //$event = new Google_Service_Calendar_Event(array(
        //    'summary' => 'Test Event',
        //    'location' => 'My place, as always',
        //    'description' => "This is a slightly more verbose description of the event.\nWhich includes a newline.",
        //    'start' => array(
        //        'date' => '2015-06-17'
        //    ),
        //    'end' => array(
        //        'date' => '2015-06-18'
        //    )
        //));

        //$event = $service->events->insert($calendarId, $event);

        //$this->view->message = "Event updated with ID <a href='" . $event->htmlLink . "'>" . $event->id . "</a>";
    }

    public function versionAction()
    {
        require_once('Zend/Version.php');
        
        $this->_helper->viewRenderer('echoMessage',null,true);
        $this->view->title = 'Zend Version';
        
        $this->view->message = Zend_Version::VERSION;
    }

    public function passwordReminderAction()
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
                        "Password: \n\n" .
                        "Kind regards,\n" .
                        "The Lochac Seneschals' Database";
            
            $mailHead = "From:{$group->email}";
            
            if(mail($mailTo, $mailSubj, $mailBody, $mailHead)) $successCount++;
            else $this->view->message .= "<div class='bad'>Sending to seneschal of {$group->groupname} failed. Try emailing manually.</div><br />\n";
            $totalCount++;
            
        }
        
        $this->view->message .= "{$successCount} out of {$totalCount} emails sent successfully.<br />\n";
    }

    public function googleListAction()
    {
        global $config;
        
        require_once('Zend/Gdata/Calendar.php');
        require_once('Zend/Gdata/ClientLogin.php');
        
        $this->_helper->viewRenderer('echoMessage', null, true);
        $this->view->title = 'GCal Event Listing';
        
        $client = Zend_Gdata_ClientLogin::getHttpClient($config->google->username,
                                                        $config->google->password,
                                                        Zend_Gdata_Calendar::AUTH_SERVICE_NAME);
        $service = new Zend_Gdata_Calendar($client);
        
        $query = $service->newEventQuery();
        $query->setUser('default');
        $query->setVisibility('private');
        $query->setProjection('full');
        $query->setOrderby('starttime');
        $query->setFutureevents('true');
        // Retrieve the event list from the calendar server
        try {
            $eventFeed = $service->getCalendarEventFeed($query);
        } catch (Zend_Gdata_App_Exception $e) {
            $this->view->message .= "Error: " . $e->getResponse();
        }
        // Iterate through the list of events, outputting them as an HTML list
        $this->view->message .= "<ul>\n";
        foreach ($eventFeed as $event) {
        $this->view->message .= "<li>" . $event->title . " (Event ID: " . $event->id . ")</li>\n";
        }
        $this->view->message .= "</ul>\n";
    }

    public function gcaltestAction()
    {
        $this->_helper->viewRenderer('echoMessage', null, true);
        $this->view->title = 'GCal api v3 test';

require_once realpath(dirname(__FILE__) . '/google-api-php-client/autoload.php');

$client_id = '147331430604-a5khue18o89cn9t235vr8k61hrf4plc4.apps.googleusercontent.com'; //Client ID
$service_account_name = '147331430604-a5khue18o89cn9t235vr8k61hrf4plc4@developer.gserviceaccount.com'; //Email Address
$key_file_location = 'lochac-sendb-test.pem'; //key.p12

$client = new Google_Client();
$client->setApplicationName("Client_Library_Examples");
$service = new Google_Service_Books($client);
///************************************************
//  If we have an access token, we can carry on.
//  Otherwise, we'll get one with the help of an
//  assertion credential. In other examples the list
//  of scopes was managed by the Client, but here
//  we have to list them manually. We also supply
//  the service account
// ************************************************/
if (isset($_SESSION['service_token'])) {
  $client->setAccessToken($_SESSION['service_token']);
}
$key = file_get_contents($key_file_location);
$cred = new Google_Auth_AssertionCredentials(
    $service_account_name,
    array('https://www.googleapis.com/auth/books'),
    $key
);
$client->setAssertionCredentials($cred);
if ($client->getAuth()->isAccessTokenExpired()) {
  $client->getAuth()->refreshTokenWithAssertion($cred);
}
$_SESSION['service_token'] = $client->getAccessToken();
///************************************************
//  We're just going to make the same call as in the
//  simple query as an example.
// ************************************************/
//$optParams = array('filter' => 'free-ebooks');
//$results = $service->volumes->listVolumes('Henry David Thoreau', $optParams);
//$this->view->message .=  "<h3>Results Of Call:</h3>\n";
//foreach ($results as $item) {
//  $this->view->message .= $item['volumeInfo']['title'], "<br /> \n";
//}

    }

}
