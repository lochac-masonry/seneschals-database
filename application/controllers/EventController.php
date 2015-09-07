<?php

class EventController extends SenDb_Controller
{
    public function indexAction()
    {
        $this->_forward('new');
    }

    protected function _emailSeneschal($seneschal)
    {
        $mailTo = $seneschal->email;

        $mailSubj = 'New Event Awaiting Approval';

        $mailBody = "Greetings {$seneschal->scaname}!\n\n" .
                    "A new event proposal has been submitted on the Lochac Seneschals' Database.\n" .
                    "At your convenience, log in using your group's username and password, review the proposal and " .
                    "edit, approve or reject as appropriate. Once approved, the event will be added to the Kingdom calendar " .
                    "and sent to Pegasus and Announce.\n" .
                    "Access the Seneschals' Database at http://lochac.sca.org/seneschal/database.\n\n" .
                    "Kind Regards,\n" .
                    "The Lochac Seneschals' Database";

        $mailHead = "From: {$seneschal->email}";

        return SenDb_Helper_Email::send($mailTo, $mailSubj, $mailBody, $mailHead);
    }

    public function newAction()
    {
        global $db;
        $groupList = $db->fetchPairs("SELECT id, groupname FROM scagroup WHERE status='live' ORDER BY groupname");

        $this->view->title = 'Submit Event Proposal';

                                                            //----------------------------------------------------------
                                                            // Build the event proposal form
                                                            //----------------------------------------------------------
        $eventForm = new SenDb_Form_Event_New(array('method' => 'post'));
        $eventForm->groupid->options = $groupList;

                                                            //----------------------------------------------------------
                                                            // Process the form
                                                            //----------------------------------------------------------
        if($eventForm->isValid($_POST)) {
            $values = $eventForm->getValues();
            unset($values['quiz'], $values['submit']);

            if($values['bookingcontact'] == '') {
                $values['bookingcontact'] = NULL;
            }
            if($values['bookingsclose'] == '') {
                $values['bookingsclose'] = NULL;
            }

            $curDateNum = date('Ymd');
            $startDateNum = str_replace('-', '', $values['startdate']);
            $endDateNum = str_replace('-', '', $values['enddate']);
            $bookDateNum = str_replace('-', '', $values['bookingsclose']);

            if($curDateNum > $startDateNum
              || $startDateNum > $endDateNum) {
                $this->addAlert('Event ends before starting, or has already started! Check the start and end dates.', SenDb_Controller::ALERT_BAD);

            } elseif(($values['bookingsclose'] != NULL)
              && ($curDateNum > $bookDateNum || $bookDateNum > $startDateNum)) {
                $this->addAlert('Bookings close after start of event or in the past! Check the close of bookings date.', SenDb_Controller::ALERT_BAD);

            } else {
                try {
                    $changed = $db->insert('events', $values);

                    if($changed == 1) {
                        $this->addAlert('Successfully added event ' . $values['name'] . '.', SenDb_Controller::ALERT_GOOD);

                        $db->setFetchMode(Zend_Db::FETCH_OBJ);
                        $seneschal = $db->fetchRow("SELECT scaname, email FROM scagroup WHERE id={$db->quote($values['groupid'],Zend_Db::INT_TYPE)}");

                        if($this->_emailSteward($values, $groupList[$values['groupid']])) {
                            $this->addAlert('Notification email sent to steward.', SenDb_Controller::ALERT_GOOD);
                        } else {
                            $this->addAlert('Failed to send notification email to steward.', SenDb_Controller::ALERT_BAD);
                        }

                        if($this->_emailSeneschal($seneschal)) {
                            $this->addAlert('Notification email sent to group seneschal.', SenDb_Controller::ALERT_GOOD);
                        } else {
                            $this->addAlert('Failed to send email to group seneschal. Please contact them manually.', SenDb_Controller::ALERT_BAD);
                        }

                    } else {
                        $this->addAlert('Creating ' . $values['name'] . ' failed. This is usually caused by a database issue. Please try again.', SenDb_Controller::ALERT_BAD);
                    }

                } catch(Exception $e) {
                    $this->addAlert('Creating ' . $values['name'] . ' failed due to a database issue. Please try again.', SenDb_Controller::ALERT_BAD);
                }

            }

        }

        $this->view->form = $eventForm;
    }

    public function listAction()
    {
        $auth = authenticate();
        global $db;
        if($auth['level'] != 'admin' && $auth['level'] != 'user') {
            throw new SenDb_Exception_NotAuthorised();
            return;
        }

        $this->view->title = 'Review Event Proposals';
        $groupList = $db->fetchPairs('SELECT id, groupname FROM scagroup ORDER BY groupname');

                                                            //----------------------------------------------------------
                                                            // Event selection - host group, past/future and approval
                                                            // Host group choice only available to admin
                                                            //----------------------------------------------------------
        $groupSelectForm = new SenDb_Form_Event_List(array('method' => 'get'));
        $groupSelectForm->groupid->options = array('all' => 'All Groups') + $groupList;

        if($auth['level'] != 'admin') {
            $groupSelectForm->groupid->disabled = true;
        }

                                                            //----------------------------------------------------------
                                                            // Process the selection form
                                                            //----------------------------------------------------------
        if($groupSelectForm->isValid($_GET)) {
            if($auth['level'] == 'admin') {
                if($groupSelectForm->getValue('groupid') == null) {
                    $groupid = 'all'; // Default value for admin
                } else {
                    $groupid = $groupSelectForm->getValue('groupid');
                }
            } else {
                $groupid = $auth['id']; // Default value for users
            }

            $status = $groupSelectForm->getValue('status');
            if($status == null) {
                $status = 'new'; // Default value for everyone
            }

            $tense = $groupSelectForm->getValue('tense');
            if($tense == null) {
                $tense = 'future'; // Default value for everyone
            }

            // Use the selected sort column, default to start date
            if(isset($_GET['sort'])) {
                switch(strtolower($_GET['sort'])) {
                    case 'name':
                        $sort = 'name';
                        break;
                    case 'lastchange':
                        $sort = 'lastchange';
                        break;
                    case 'startdate':
                    default:
                        $sort = 'startdate';
                        break;
                }
            } else {
                $sort = 'startdate';
            }

            // Retrieve relevant events.
            $sql = "SELECT eventid, name, startdate, lastchange FROM events WHERE status={$db->quote($status)} ";
            if($groupid != 'all') {
                $sql .= "AND groupid={$db->quote($groupid,Zend_Db::INT_TYPE)} ";
            }
            if($tense == 'future') {
                $sql .= "AND CURDATE() <= startdate ";
            }
            if($tense == 'past') {
                $sql .= "AND CURDATE() > startdate ";
            }
            $sql .= "ORDER BY {$sort}";

            $db->setFetchMode(Zend_Db::FETCH_OBJ);
            $events = $db->fetchAll($sql);
        }

                                                            //----------------------------------------------------------
                                                            // Pass retrieved events to the view, and render the form
                                                            //----------------------------------------------------------
        if(isset($events)) {
            $this->view->events = $events;
        } else {
            $this->view->events = array();
        }

        if($auth['level'] != 'admin') {
            $groupSelectForm->setDefaults(array('groupid' => $auth['id']));
        }
        $this->view->groupSelectForm = $groupSelectForm;
    }

    protected function _emailSteward($values, $hostGroupName)
    {
        $mailTo = $values['stewardemail'];

        $mailSubj = "Event Updated on Lochac Seneschals' Database";

        $mailBody = "Greetings {$values['stewardname']}!\n\n" .
                    "Your event on the Lochac Seneschals' Database has been updated.\n" .
                    "If the event has been marked with a status of 'approved', please go ahead in\n" .
                    "running the event. Get in contact with your seneschal if you wish to make changes.\n\n" .
                    "*Event Details*\n" .
                    "Name:\t\t" . $values['name'] . "\n" .
                    "Host Group:\t" . $hostGroupName . "\n" .
                    "Start date:\t" . $values['startdate'] . "\n" .
                    "End date:\t" . $values['enddate'] . "\n" .
                    "Location:\n" . $values['location'] . "\n" .
                    "Event type:\t" . $values['type'] . "\n" .
                    "Description:\n" . $values['description'] . "\n" .
                    "Price:\n" . $values['price'] . "\n\n" .
                    "*Steward Details*\n" .
                    "Real Name:\t" . $values['stewardreal'] . "\n" .
                    "SCA Name:\t" . $values['stewardname'] . "\n" .
                    "Email Address:\t" . $values['stewardemail'] . "\n\n" .
                    "*Booking Details*\n" .
                    "Booking Contact:\n" . $values['bookingcontact'] . "\n" .
                    "Bookings Close:\t" . $values['bookingsclose'] . "\n\n" .
                    "STATUS:\t\t" . strtoupper($values['status']) . "\n\n" .
                    "If you need to make corrections, contact the seneschal of {$hostGroupName}.\n\n" .
                    "Best of luck with your event,\n" .
                    "The Lochac Seneschals' Database";

        $mailHead = "From: {$values['stewardemail']}";

        return SenDb_Helper_Email::send($mailTo, $mailSubj, $mailBody, $mailHead);
    }

    protected function _emailAnnounce($values, $hostGroupName)
    {
        $mailTo = "announce@lochac.sca.org";

        $mailSubj = "Event Notification for {$values['name']} on {$values['startdate']} ({$hostGroupName})";

        $mailBody = "Event notification for {$values['name']} on {$values['startdate']}\n" .
                    "The following announcement has been generated from http://lochac.sca.org/seneschal/database\n" .
                    "and forwarded to Lochac-Announce at the request of the Event Steward.\n\n" .
                    "EVENT DETAILS\n=============\n" .
                    "Event Name:\t" . $values['name'] . "\n" .
                    "Host Group:\t" . $hostGroupName . "\n";

        if($values['startdate'] == $values['enddate']) {
            $mailBody .= "Date:\t\t" . date('l, F jS Y', strtotime($values['startdate'])) . "\n";
        } else {
            $mailBody .= "Start date:\t" . date('l, F jS Y', strtotime($values['startdate'])) . "\n" .
                         "End date:\t" . date('l, F jS Y', strtotime($values['enddate'])) . "\n";
        }

        $mailBody .= "Event type:\t" . $values['type'] . "\n" .
                     "Location:\n" . $values['location'] . "\n\n" .
                     "STEWARD DETAILS\n===============\n" .
                     "Name:\t\t" . $values['stewardname'] . "\n" .
                     "Email Address:\t" . $values['stewardemail'] . "\n\n" .
                     "BOOKING DETAILS\n===============\n";

        if(empty($values['bookingcontact'])
          || empty($values['bookingsclose'])) {
            $mailBody .= "Bookings not required.\n";
        } else {
            $mailBody .= "Bookings Close:\t" . date('l, F jS Y', strtotime($values['bookingsclose'])) . "\n" .
                         "Booking Contact:\n" . $values['bookingcontact'] . "\n";
        }

        $mailBody .= "Price:\n" . $values['price'] . "\n\n" .
                     "DESCRIPTION\n===========\n" . $values['description'];

        $mailHead = "From: information@lochac.sca.org";

        return SenDb_Helper_Email::send($mailTo, $mailSubj, $mailBody, $mailHead);
    }

    protected function _emailPegasus($values, $hostGroup)
    {
        $mailTo = "pegasus_events@lochac.sca.org";

        $mailSubj = "Event Notification for {$values['name']} on {$values['startdate']} ({$hostGroup['groupname']})";

        $mailBody = "Event notification for {$values['name']} on {$values['startdate']}\n\n";

        if($values['startdate'] == $values['enddate']) {
            $mailBody .= date('j M Y. ', strtotime($values['startdate']));
        } else {
            $mailBody .= date('j M Y - ', strtotime($values['startdate'])) .
                         date('j M Y. ', strtotime($values['enddate']));
        }

        $mailBody .= $values['name'] . ". {$hostGroup['type']} of {$hostGroup['groupname']}, {$hostGroup['state']}\n" .
                     "Site: {$values['location']}. Cost: {$values['price']}. {$values['description']} " .
                     "Steward: {$values['stewardname']}, {$values['stewardemail']}. ";

        if(empty($values['bookingcontact'])
          || empty($values['bookingsclose'])) {
            $mailBody .= "Bookings not required.\n\n";
        } else {
            $mailBody .= "Bookings: {$values['bookingcontact']} by " . date('l, F jS Y', strtotime($values['bookingsclose'])) . "\n\n";
        }

        $mailBody .= "Kind regards,\nThe Lochac Seneschals' Database";

        $mailHead = "From: information@lochac.sca.org";

        return SenDb_Helper_Email::send($mailTo, $mailSubj, $mailBody, $mailHead);
    }

    protected function _getGoogleCalendarService()
    {
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

        return new Google_Service_Calendar($client);
    }

    protected function _updateCalendar($values, $hostGroupName, $eventId)
    {
        global $config;
        $calendarId = $config->google->calendarId;

        try {
            $service = $this->_getGoogleCalendarService();

            if (empty($eventId)) {
                $event = new Google_Service_Calendar_Event();
            } else {
                $event = $service->events->get($calendarId, $eventId);
            }

            $event->summary = $values['name'] . " (" . $hostGroupName . ")";
            $event->location = $values['location'];
            $event->description = "Steward:\t" . $values['stewardname'] . "\n"
                                . "Email:\t" . $values['stewardemail'] . "\n\n"
                                . $values['description'];
            $event->start = array('date' => $values['startdate']);
            // Google uses exclusive end dates, so we add a day to the end date
            $event->end = array('date' => date('Y-m-d',strtotime($values['enddate']) + 60*60*24));

            if (empty($eventId)) {
                $event = $service->events->insert($calendarId, $event);
            } else {
                $event = $service->events->update($calendarId, $eventId, $event);
            }

            return $event->id;

        } catch (Google_Service_Exception $e) {
            $this->addAlert('GCal error: ' . $e->getMessage(), SenDb_Controller::ALERT_BAD);
            return false;
        }

    }

    protected function _deleteCalendar($eventId)
    {
        global $config;
        $calendarId = $config->google->calendarId;

        if (empty($eventId)) {
            return false;
        }

        try {
            $service = $this->_getGoogleCalendarService();

            $service->events->delete($calendarId, $eventId);

            return true;

        } catch (Google_Service_Exception $e) {
            $this->addAlert('GCal error: ' . $e->getMessage(), SenDb_Controller::ALERT_BAD);
            return false;
        }

    }

    public function editAction()
    {
        $auth = authenticate();
        if($auth['level'] != 'admin'
          && $auth['level'] != 'user') {
            throw new SenDb_Exception_NotAuthorised();
            return;
        }
        global $db;
        $groupList = $db->fetchPairs("SELECT id, groupname FROM scagroup WHERE status='live' ORDER BY groupname");

        $this->view->title = 'Edit Event Proposal';

        if(isset($_GET['eventid'])
          && is_numeric($_GET['eventid'])) {
            $id = floor($_GET['eventid']);
            if(0 == $db->fetchOne("SELECT COUNT(*) FROM events WHERE eventid={$db->quote($id,Zend_Db::INT_TYPE)}")) {
                $this->_forward('new');
                return;
            }
        } else {
            $this->_forward('new');
            return;
        }

                                                            //----------------------------------------------------------
                                                            // Build the event editing and approval form
                                                            //----------------------------------------------------------
        $eventForm = new SenDb_Form_Event_Edit(array('method' => 'post'));
        $eventForm->groupid->options = $groupList;

                                                            //----------------------------------------------------------
                                                            // Process event form
                                                            //----------------------------------------------------------
        if($eventForm->isValid($_POST)) {
            $values = $eventForm->getValues();
            $sendTo = $values['sendto'];
            $googleId = $values['googleid'];

            // Change values to suit DB.
            unset($values['sendto'], $values['googleid'], $values['submit']);
            if($values['bookingcontact'] == '') {
                $values['bookingcontact'] = NULL;
            }
            if($values['bookingsclose'] == '') {
                $values['bookingsclose'] = NULL;
            }
            if($values['status'] != $db->fetchOne("SELECT status FROM events WHERE eventid={$db->quote($id,Zend_Db::INT_TYPE)}")) {
                $values['lastchange'] = new Zend_Db_Expr("CURRENT_TIMESTAMP");
            }

            // Dates can be compared as numbers by removing '-'
            $curDateNum = date('Ymd');
            $startDateNum = str_replace('-', '', $values['startdate']);
            $endDateNum = str_replace('-', '', $values['enddate']);
            $bookDateNum = str_replace('-', '', $values['bookingsclose']);

                                                            //----------------------------------------------------------
                                                            // Check that dates are sensible
                                                            //----------------------------------------------------------
            if($curDateNum > $startDateNum
              || $startDateNum > $endDateNum) {
                $this->addAlert('Event ends before starting, or has already started! Check the start and end dates.', SenDb_Controller::ALERT_BAD);

            } elseif(($values['bookingsclose'] != NULL)
              && ($curDateNum > $bookDateNum || $bookDateNum > $startDateNum)) {
                $this->addAlert('Bookings close after start of event or in the past! Check the close of bookings date.', SenDb_Controller::ALERT_BAD);

            } elseif($auth['level'] == 'user'
              && $auth['id'] != $db->fetchOne("SELECT groupid FROM events WHERE eventid={$db->quote($id,Zend_Db::INT_TYPE)}")) {
                $this->addAlert('Can only edit events assigned to your group, sorry.', SenDb_Controller::ALERT_BAD);

            } else {
                // Update.
                try {
                    $changed = $db->update(
                        'events',
                        $values,
                        "eventid={$db->quote($id,Zend_Db::INT_TYPE)}"
                    );

                                                            //----------------------------------------------------------
                                                            // Check that the update worked
                                                            //----------------------------------------------------------
                    if($changed == 1) {
                        $this->addAlert('Event details updated in database.', SenDb_Controller::ALERT_GOOD);
                    } elseif($changed == 0) {
                        $this->addAlert('Event record unchanged in database.');
                    } else {
                        $this->addAlert('Editing ' . $values['name'] . ' failed. The event might not exist. Refresh to check.', SenDb_Controller::ALERT_BAD);
                    }

                                                            //----------------------------------------------------------
                                                            // Email the steward
                                                            //----------------------------------------------------------
                    if($this->_emailSteward($values, $groupList[$values['groupid']])) {
                        $this->addAlert('Notification email sent to steward.', SenDb_Controller::ALERT_GOOD);
                    } else {
                        $this->addAlert('Failed to send notification email to steward.', SenDb_Controller::ALERT_BAD);
                    }

                                                            //----------------------------------------------------------
                                                            // If event approved and Pegasus selected, send to Pegasus
                                                            //----------------------------------------------------------
                    if($sendTo != null
                      && in_array('pegasus', $sendTo)
                      && $values['status'] == 'approved') {
                        $db->setFetchMode(Zend_Db::FETCH_ASSOC);
                        $hostGroup = $db->fetchRow("SELECT groupname, type, state FROM scagroup WHERE id={$db->quote($values['groupid'],Zend_Db::INT_TYPE)}");

                        if($this->_emailPegasus($values, $hostGroup)) {
                            $this->addAlert('Event submitted to Pegasus.', SenDb_Controller::ALERT_GOOD);
                        } else {
                            $this->addAlert('Failed to submit event to Pegasus.', SenDb_Controller::ALERT_BAD);
                        }
                    }

                                                            //----------------------------------------------------------
                                                            // If event not approved, make sure it isn't on the calendar
                                                            //----------------------------------------------------------
                    if($values['status'] != 'approved') {
                        if(!empty($googleId)) {
                            $result = $this->_deleteCalendar($googleId);

                            if($result === false) {
                                $this->addAlert('Failed to remove event from Kingdom Calendar.', SenDb_Controller::ALERT_BAD);
                            } else {
                                $this->addAlert('Removed event from Kingdom Calendar.', SenDb_Controller::ALERT_GOOD);

                                // store updated eventId
                                $changed = $db->update(
                                    'events',
                                    array('googleid' => NULL),
                                    "eventid={$db->quote($id,Zend_Db::INT_TYPE)}"
                                );
                                if($changed == 0 || $changed == 1) {
                                    $this->addAlert('Stored GCal event ID in database.', SenDb_Controller::ALERT_GOOD);
                                } else {
                                    $this->addAlert('Failed to store GCal event ID in database.', SenDb_Controller::ALERT_BAD);
                                }
                            }
                        }
                                                            //----------------------------------------------------------
                                                            // If event approved and calendar selected, add to calendar
                                                            //----------------------------------------------------------
                    } else {
                        if($sendTo != null
                          && in_array('calendar', $sendTo)) {
                            $result = $this->_updateCalendar(
                                $values,
                                $groupList[$values['groupid']],
                                $googleId
                            );

                            if($result === false) {
                                $this->addAlert('Failed to update Kingdom Calendar.', SenDb_Controller::ALERT_BAD);
                            } else {
                                $this->addAlert('Updated Kingdom Calendar.', SenDb_Controller::ALERT_GOOD);

                                // store updated eventId
                                $changed = $db->update(
                                    'events',
                                    array('googleid' => $result),
                                    "eventid={$db->quote($id,Zend_Db::INT_TYPE)}"
                                );
                                if($changed == 0 || $changed == 1) {
                                    $this->addAlert('Stored GCal event ID in database.', SenDb_Controller::ALERT_GOOD);
                                } else {
                                    $this->addAlert('Failed to store GCal event ID in database.', SenDb_Controller::ALERT_BAD);
                                }
                            }
                        }
                    }

                                                            //----------------------------------------------------------
                                                            // If event approved and Announce selected, send to Announce
                                                            //----------------------------------------------------------
                    if($sendTo != null
                      && in_array('announce', $sendTo)
                      && $values['status'] == 'approved') {
                        if($this->_emailAnnounce($values, $groupList[$values['groupid']])) {
                            $this->addAlert('Notification email sent to Lochac-Announce.', SenDb_Controller::ALERT_GOOD);
                        } else {
                            $this->addAlert('Failed to send notification email to Lochac-Announce.', SenDb_Controller::ALERT_BAD);
                        }
                    }

                } catch(Exception $e) {
                    $this->addAlert('Editing ' . $values['name'] . ' failed due to a database error. Please try again.', SenDb_Controller::ALERT_BAD);
                }

            }
        }

                                                            //----------------------------------------------------------
                                                            // Populate form with current details, and render
                                                            //----------------------------------------------------------
        $db->setFetchMode(Zend_Db::FETCH_ASSOC);
        $defaults = $db->fetchRow("SELECT name, groupid, startdate, enddate, location, type, description, price, stewardreal, " .
                                  "stewardname, stewardemail, bookingcontact, bookingsclose, status, googleid " .
                                  "FROM events WHERE eventid={$db->quote($id,Zend_Db::INT_TYPE)}");
        $eventForm->setDefaults($defaults);

        $this->view->form = $eventForm;
    }
}
