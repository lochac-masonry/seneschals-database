<?php

namespace Application\Controller;

use Application\Form;
use Zend\Db\Sql\{Insert, Select, Sql, Update};
use Zend\Uri\Http;

class EventController extends BaseController
{
    private $googleMetadata;

    public function indexAction()
    {
        return $this->forwardToAction('new');
    }

    private function emailSteward($values, $hostGroupName)
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
                    "Setup time(s):\n" . $values['setupTime'] . "\n" .
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
                    "STATUS:\t\t" . strtoupper(isset($values['status']) ? $values['status'] : '') . "\n\n" .
                    "If you need to make corrections, contact the seneschal of {$hostGroupName}.\n\n" .
                    "Best of luck with your event,\n" .
                    "The Lochac Seneschals' Database";

        $mailHead = "From: {$values['stewardemail']}";

        return $this->sendEmail($mailTo, $mailSubj, $mailBody, $mailHead);
    }

    private function emailSeneschal($seneschal)
    {
        $url = $this->url()->fromRoute(null, [], ['uri' => (new Http())->setScheme('https')]);
        $mailTo = $seneschal['email'];

        $mailSubj = 'New Event Awaiting Approval';

        $mailBody = "Greetings {$seneschal['scaname']}!\n\n" .
                    "A new event proposal has been submitted on the Lochac Seneschals' Database.\n" .
                    "At your convenience, log in using your group's username and password, " .
                    "review the proposal and edit, approve or reject as appropriate. " .
                    "Once approved, the event will be added to the Kingdom calendar " .
                    "and sent to Pegasus and Announce.\n" .
                    "Access the Seneschals' Database at {$url}.\n\n" .
                    "Kind Regards,\n" .
                    "The Lochac Seneschals' Database";

        $mailHead = "From: {$seneschal['email']}";

        return $this->sendEmail($mailTo, $mailSubj, $mailBody, $mailHead);
    }

    public function newAction()
    {
        $this->layout()->title = 'Submit Event Proposal';
        $db = $this->getDb();

        $groupList = $this->arrayIndex(
            $db->query("SELECT id, groupname FROM scagroup WHERE status = 'live' ORDER BY groupname", []),
            'id',
            'groupname'
        );

        $detailsForm = new Form\Event\Event($groupList);
        $viewModel = [
            'detailsForm' => $detailsForm,
        ];

        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $viewModel;
        }

        $detailsForm->setData($request->getPost());
        if (!$detailsForm->isValid()) {
            return $viewModel;
        }

        // Form is valid - transform the values into those expected by the database.
        $rawValues = $detailsForm->getData();
        $values = $rawValues['eventGroup'] + $rawValues['stewardGroup'] + $rawValues['bookingGroup'];

        $db->query(
            (new Sql($db))->buildSqlString(
                (new Insert('events'))
                    ->values($values)
            ),
            $db::QUERY_MODE_EXECUTE
        );
        $this->addAlert("Successfully added event {$values['name']}.", self::ALERT_GOOD);

        if ($this->emailSteward($values, $groupList[$values['groupid']])) {
            $this->addAlert('Notification email sent to steward.', self::ALERT_GOOD);
        } else {
            $this->addAlert('Failed to send notification email to steward.', self::ALERT_BAD);
        }

        $seneschal = (array) $db->query(
            (new Sql($db))->buildSqlString(
                (new Select())
                    ->columns(['scaname', 'email'])
                    ->from('scagroup')
                    ->where(['id' => $values['groupid']])
            ),
            []
        )->toArray()[0];
        if ($this->emailSeneschal($seneschal)) {
            $this->addAlert('Notification email sent to group seneschal.', self::ALERT_GOOD);
        } else {
            $this->addAlert(
                'Failed to send email to group seneschal. Please contact them manually.',
                self::ALERT_BAD
            );
        }

        return $viewModel;
    }

    public function listAction()
    {
        $this->layout()->title = 'Review Event Proposals';
        $db = $this->getDb();
        $authResponse = $this->ensureAuthLevel(['admin', 'user']);
        if ($authResponse) {
            return $authResponse;
        }

        $groupList = $this->arrayIndex(
            $db->query('SELECT id, groupname FROM scagroup ORDER BY groupname', []),
            'id',
            'groupname'
        );

                                                            //----------------------------------------------------------
                                                            // List filter form
                                                            //----------------------------------------------------------
        $filterForm = new Form\Event\ListFilter(['all' => 'All Groups'] + $groupList);
        $filterForm->get('groupid')->setAttribute('disabled', $this->auth['level'] != 'admin');
        $viewModel = [
            'filterForm' => $filterForm,
            'events'     => [],
        ];

        $request = $this->getRequest();
        $queryData = $request->getQuery();
        $filterForm->setData([
            'groupid' => $this->auth['level'] == 'admin' ? ($queryData['groupid'] ?: 'all') : $this->auth['id'],
            'status'  => $queryData['status'] ?: 'new',
            'tense'   => $queryData['tense'] ?: 'future',
        ]);

        if (!$filterForm->isValid()) {
            return $viewModel;
        }

                                                            //----------------------------------------------------------
                                                            // Build the query to display the list
                                                            //----------------------------------------------------------
        $values = $filterForm->getData();
        $select = (new Select())
            ->columns(['eventid', 'name', 'startdate', 'lastchange'])
            ->from('events')
            ->order('startdate');

        if ($values['groupid'] != 'all') {
            $select->where(['groupid' => $values['groupid']]);
        }
        if ($values['status'] != 'all') {
            $select->where(['status' => $values['status']]);
        }
        if ($values['tense'] != 'both') {
            $operator = $values['tense'] == 'future' ? '>=' : '<';
            $select->where('startdate ' . $operator . ' CURDATE()');
        }

        $viewModel['events'] = $db->query(
            (new Sql($db))->buildSqlString($select),
            []
        )->toArray();

        return $viewModel;
    }

    private function emailAnnounce($values, $hostGroupName)
    {
        $url = $this->url()->fromRoute(null, [], ['uri' => (new Http())->setScheme('https')]);
        $mailTo = "announce@lochac.sca.org";

        $mailSubj = "Event Notification for {$values['name']} on {$values['startdate']} ({$hostGroupName})";

        $mailBody = "Event notification for {$values['name']} on {$values['startdate']}\n" .
                    "The following announcement has been generated from {$url}\n" .
                    "and forwarded to Lochac-Announce at the request of the Event Steward.\n\n" .
                    "EVENT DETAILS\n=============\n" .
                    "Event Name:\t" . $values['name'] . "\n" .
                    "Host Group:\t" . $hostGroupName . "\n";

        if ($values['startdate'] == $values['enddate']) {
            $mailBody .= "Date:\t\t" . date('l, F jS Y', strtotime($values['startdate'])) . "\n";
        } else {
            $mailBody .= "Start date:\t" . date('l, F jS Y', strtotime($values['startdate'])) . "\n" .
                         "End date:\t" . date('l, F jS Y', strtotime($values['enddate'])) . "\n";
        }
        if (!empty($values['setupTime'])) {
            $mailBody .= "Setup time(s):\n" . $values['setupTime'] . "\n";
        }

        $mailBody .= "Event type:\t" . $values['type'] . "\n" .
                     "Location:\n" . $values['location'] . "\n\n" .
                     "STEWARD DETAILS\n===============\n" .
                     "Name:\t\t" . $values['stewardname'] . "\n" .
                     "Email Address:\t" . $values['stewardemail'] . "\n\n" .
                     "BOOKING DETAILS\n===============\n";

        if (empty($values['bookingcontact']) || empty($values['bookingsclose'])) {
            $mailBody .= "Bookings not required.\n";
        } else {
            $mailBody .= "Bookings Close:\t" . date('l, F jS Y', strtotime($values['bookingsclose'])) . "\n" .
                         "Booking Contact:\n" . $values['bookingcontact'] . "\n";
        }

        $mailBody .= "Price:\n" . $values['price'] . "\n\n" .
                     "DESCRIPTION\n===========\n" . $values['description'];

        $mailHead = "From: information@lochac.sca.org";

        return $this->sendEmail($mailTo, $mailSubj, $mailBody, $mailHead);
    }

    private function emailPegasus($values, $hostGroup)
    {
        $mailTo = "pegasus_events@lochac.sca.org";

        $mailSubj = "Event Notification for {$values['name']} on {$values['startdate']} ({$hostGroup['groupname']})";

        $mailBody = "Event notification for {$values['name']} on {$values['startdate']}\n\n";

        if ($values['startdate'] == $values['enddate']) {
            $mailBody .= date('j M Y. ', strtotime($values['startdate']));
        } else {
            $mailBody .= date('j M Y - ', strtotime($values['startdate'])) .
                         date('j M Y. ', strtotime($values['enddate']));
        }

        $mailBody .= $values['name'] . ". {$hostGroup['type']} of {$hostGroup['groupname']}, {$hostGroup['state']}\n" .
                     "Site: {$values['location']}. Cost: {$values['price']}. ";

        if (!empty($values['setupTime'])) {
            $mailBody .= "Setup time(s): {$values['setupTime']}. ";
        }

        $mailBody .= "{$values['description']} Steward: {$values['stewardname']}, {$values['stewardemail']}. ";

        if (empty($values['bookingcontact']) || empty($values['bookingsclose'])) {
            $mailBody .= "Bookings not required.\n\n";
        } else {
            $mailBody .= "Bookings: {$values['bookingcontact']} by " .
                         date('l, F jS Y', strtotime($values['bookingsclose'])) . "\n\n";
        }

        $mailBody .= "Kind regards,\nThe Lochac Seneschals' Database";

        $mailHead = "From: information@lochac.sca.org";

        return $this->sendEmail($mailTo, $mailSubj, $mailBody, $mailHead);
    }

    private function getGoogleMetadata()
    {
        if (!$this->googleMetadata) {
            $this->googleMetadata = json_decode(file_get_contents('google-key.json'));
        }
        return $this->googleMetadata;
    }

    private function getGoogleCalendarService()
    {
        $googleMetadata = $this->getGoogleMetadata();

        $credentials = new \Google_Auth_AssertionCredentials(
            $googleMetadata->client_email,
            \Google_Service_Calendar::CALENDAR,
            $googleMetadata->private_key
        );

        $client = new \Google_Client();
        $client->setAssertionCredentials($credentials);

        if ($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion();
        }

        return new \Google_Service_Calendar($client);
    }

    private function updateCalendar($values, $hostGroupName, $eventId)
    {
        $googleMetadata = $this->getGoogleMetadata();
        $calendarId = $googleMetadata->calendar_id;

        try {
            $service = $this->getGoogleCalendarService();

            if (empty($eventId)) {
                $event = new \Google_Service_Calendar_Event();
            } else {
                $event = $service->events->get($calendarId, $eventId);
            }

            $event->summary = $values['name'] . " (" . $hostGroupName . ")";
            $event->location = $values['location'];
            $event->description = "Steward:\t" . $values['stewardname'] . "\n"
                                . "Email:\t" . $values['stewardemail'] . "\n\n"
                                . $values['description'];
            $event->start = ['date' => $values['startdate']];
            // Google uses exclusive end dates, so we add a day to the end date
            $event->end = ['date' => date('Y-m-d', strtotime($values['enddate']) + 60 * 60 * 24)];

            if (empty($eventId)) {
                $event = $service->events->insert($calendarId, $event);
            } else {
                $event = $service->events->update($calendarId, $eventId, $event);
            }

            return $event->id;
        } catch (\Google_Service_Exception $e) {
            $this->addAlert('GCal error: ' . $e->getMessage(), self::ALERT_BAD);
            return false;
        }
    }

    private function deleteCalendar($eventId)
    {
        $googleMetadata = $this->getGoogleMetadata();
        $calendarId = $googleMetadata->calendar_id;

        if (empty($eventId)) {
            return false;
        }

        try {
            $service = $this->getGoogleCalendarService();

            $service->events->delete($calendarId, $eventId);

            return true;
        } catch (\Google_Service_Exception $e) {
            $this->addAlert('GCal error: ' . $e->getMessage(), self::ALERT_BAD);
            return false;
        }
    }

    public function editAction()
    {
        $this->layout()->title = 'Edit Event Proposal';
        $db = $this->getDb();
        $authResponse = $this->ensureAuthLevel(['admin', 'user']);
        if ($authResponse) {
            return $authResponse;
        }

        $groupList = $this->arrayIndex(
            $db->query("SELECT id, groupname FROM scagroup WHERE status = 'live' ORDER BY groupname", []),
            'id',
            'groupname'
        );

                                                            //----------------------------------------------------------
                                                            // Check that the eventid provided exists and
                                                            // that the user is allowed to access it.
                                                            //----------------------------------------------------------
        $request = $this->getRequest();
        $eventId = $request->getQuery()['eventid'];
        if (!is_numeric($eventId)) {
            return $this->forwardToAction('new');
        }
        $eventId = floor($eventId); // Convert to int.
        $initialData = $db->query(
            (new Sql($db))->buildSqlString(
                (new Select())
                    ->from('events')
                    ->where(['eventid' => $eventId])
            ),
            []
        )->toArray();
        if (count($initialData) == 0) {
            return $this->notFoundAction();
        }
        $initialData = (array) $initialData[0];
        if ($this->auth['level'] != 'admin' && $this->auth['id'] != $initialData['groupid']) {
            return $this->notFoundAction();
        }

                                                            //----------------------------------------------------------
                                                            // Build details form
                                                            //----------------------------------------------------------
        $detailsForm = new Form\Event\Event($groupList, true);
        $detailsForm->setData([
            'eventGroup' => array_intersect_key($initialData, array_flip([
                'name',
                'groupid',
                'startdate',
                'enddate',
                'setupTime',
                'location',
                'type',
                'description',
                'price',
            ])),
            'stewardGroup' => array_intersect_key($initialData, array_flip([
                'stewardreal',
                'stewardname',
                'stewardemail',
            ])),
            'bookingGroup' => array_intersect_key($initialData, array_flip([
                'bookingcontact',
                'bookingsclose',
            ])),
            'submitGroup' => [
                'status' => $initialData['status'],
                'sendto' => ['pegasus', 'calendar', 'announce'], // Enable all publicity by default.
            ],
        ]);
        $viewModel = [
            'detailsForm' => $detailsForm,
        ];

        if (!$request->isPost()) {
            return $viewModel;
        }

                                                            //----------------------------------------------------------
                                                            // Process event form
                                                            //----------------------------------------------------------
        $detailsForm->setData($request->getPost());
        if (!$detailsForm->isValid()) {
            return $viewModel;
        }
        $rawValues = $detailsForm->getData();
        $values = $rawValues['eventGroup'] + $rawValues['stewardGroup'] + $rawValues['bookingGroup'];
        $values['status'] = $rawValues['submitGroup']['status'];
        $sendTo = $rawValues['submitGroup']['sendto'] ?: [];

        $db->query(
            (new Sql($db))->buildSqlString(
                (new Update('events'))
                    ->set($values)
                    ->where(['eventid' => $eventId])
            ),
            $db::QUERY_MODE_EXECUTE
        );
        $this->addAlert("Successfully updated event {$values['name']} in database.", self::ALERT_GOOD);

                                                            //----------------------------------------------------------
                                                            // Email the steward
                                                            //----------------------------------------------------------
        if ($this->emailSteward($values, $groupList[$values['groupid']])) {
            $this->addAlert('Notification email sent to steward.', self::ALERT_GOOD);
        } else {
            $this->addAlert('Failed to send notification email to steward.', self::ALERT_BAD);
        }

                                                            //----------------------------------------------------------
                                                            // If event approved and Pegasus selected, send to Pegasus
                                                            //----------------------------------------------------------
        if (in_array('pegasus', $sendTo) && $values['status'] == 'approved') {
            $hostGroup = (array) $db->query(
                (new Sql($db))->buildSqlString(
                    (new Select())
                        ->columns(['groupname', 'type', 'state'])
                        ->from('scagroup')
                        ->where(['id' => $values['groupid']])
                ),
                []
            )->toArray()[0];

            if ($this->emailPegasus($values, $hostGroup)) {
                $this->addAlert('Event submitted to Pegasus.', self::ALERT_GOOD);
            } else {
                $this->addAlert('Failed to submit event to Pegasus.', self::ALERT_BAD);
            }
        }

                                                            //----------------------------------------------------------
                                                            // If event not approved, make sure it isn't on the calendar
                                                            //----------------------------------------------------------
        if ($values['status'] != 'approved') {
            if (!empty($initialData['googleid'])) {
                $result = $this->deleteCalendar($initialData['googleid']);

                if ($result === false) {
                    $this->addAlert('Failed to remove event from Kingdom Calendar.', self::ALERT_BAD);
                } else {
                    $this->addAlert('Removed event from Kingdom Calendar.', self::ALERT_GOOD);

                    // store updated googleId
                    $db->query(
                        (new Sql($db))->buildSqlString(
                            (new Update('events'))
                                ->set(['googleid' => null])
                                ->where(['eventid' => $eventId])
                        ),
                        $db::QUERY_MODE_EXECUTE
                    );
                    $this->addAlert('Removed GCal event ID from database.', self::ALERT_GOOD);
                }
            }
                                                            //----------------------------------------------------------
                                                            // If event approved and calendar selected, add to calendar
                                                            //----------------------------------------------------------
        } else {
            if (in_array('calendar', $sendTo)) {
                $result = $this->updateCalendar(
                    $values,
                    $groupList[$values['groupid']],
                    isset($initialData['googleid']) ? $initialData['googleid'] : null
                );

                if ($result === false) {
                    $this->addAlert('Failed to update Kingdom Calendar.', self::ALERT_BAD);
                } else {
                    $this->addAlert('Updated Kingdom Calendar.', self::ALERT_GOOD);

                    // store updated googleId
                    $db->query(
                        (new Sql($db))->buildSqlString(
                            (new Update('events'))
                                ->set(['googleid' => $result])
                                ->where(['eventid' => $eventId])
                        ),
                        $db::QUERY_MODE_EXECUTE
                    );
                    $this->addAlert('Stored GCal event ID in database.', self::ALERT_GOOD);
                }
            }
        }

                                                            //----------------------------------------------------------
                                                            // If event approved and Announce selected, send to Announce
                                                            //----------------------------------------------------------
        if (in_array('announce', $sendTo) && $values['status'] == 'approved') {
            if ($this->emailAnnounce($values, $groupList[$values['groupid']])) {
                $this->addAlert('Notification email sent to Lochac-Announce.', self::ALERT_GOOD);
            } else {
                $this->addAlert('Failed to send notification email to Lochac-Announce.', self::ALERT_BAD);
            }
        }

        return $viewModel;
    }
}
