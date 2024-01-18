<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Form;
use Application\LazyQuahogClient;
use Google;
use Google\Service\Calendar;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Sql\{Expression, Insert, Select, Sql, Update};
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Uri\Uri;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;

class EventController extends AbstractActionController
{
    public function __construct(
        private AdapterInterface $db,
        private LazyQuahogClient $lazyQuahogClient,
        private PhpRenderer $renderer,
        private string $googleCalendarId
    ) {
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
                    "Timetable:\n" . $values['timetable'] . "\n" .
                    "Location:\n" . $values['location'] . "\n" .
                    "Event type:\t" . $values['type'] . "\n" .
                    "Description:\n" . $values['description'] . "\n" .
                    "Price:\n" . $values['price'] . "\n" .
                    "Website:\t" . (isset($values['website']) ? $values['website'] : '') . "\n\n" .
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

        return $this->sendEmail($mailTo, $mailSubj, $mailBody);
    }

    private function emailSeneschal($seneschal)
    {
        $url = $this->url()->fromRoute('home', [], ['force_canonical' => true]);
        $mailTo = $seneschal['email'];

        $mailSubj = 'New Event Awaiting Approval';

        $mailBody = "Greetings {$seneschal['sca_name']}!\n\n" .
                    "A new event proposal has been submitted on the Lochac Seneschals' Database.\n" .
                    "At your convenience, log in using your group's username and password, " .
                    "review the proposal and edit, approve or reject as appropriate. " .
                    "Once approved, the event will be added to the Kingdom calendar " .
                    "and sent to Pegasus and Announce.\n" .
                    "Access the Seneschals' Database at {$url}.\n\n" .
                    "Kind Regards,\n" .
                    "The Lochac Seneschals' Database";

        return $this->sendEmail($mailTo, $mailSubj, $mailBody);
    }

    /**
     * Scan uploaded files using ClamAV and delete any threats.
     */
    private function scanFilesWithAntiVirus($rawFiles)
    {
        $quahogClient = $this->lazyQuahogClient->getClient();
        $quahogClient->startSession();
        $cleanFiles = [];
        foreach ($rawFiles as $file) {
            $result = $quahogClient->scanFile(realpath($file['tmp_name']));
            if (!$result->isOk()) {
                if (file_exists($file['tmp_name'])) {
                    unlink($file['tmp_name']);
                }
                $this->alert()->bad("Attachment '{$file['name']}' was blocked by anti-virus scanner.");
            } else {
                $cleanFiles[] = $file;
            }
        }
        $quahogClient->endSession();
        return $cleanFiles;
    }

    /**
     * Insert an attachment record based on a newly-uploaded file.
     */
    private function insertAttachment($file, $eventId)
    {
        $this->db->query(
            (new Sql($this->db))->buildSqlString(
                (new Insert('event_attachment'))
                    ->values([
                        'event_id' => $eventId,
                        'location' => $file['tmp_name'],
                        'name'     => $file['name'],
                        'size'     => $file['size'],
                    ])
            ),
            $this->db::QUERY_MODE_EXECUTE
        );
    }

    /**
     * Delete an array of files from the filesystem.
     */
    private function deleteFiles($files)
    {
        foreach ($files as $file) {
            if (isset($file['tmp_name']) && file_exists($file['tmp_name'])) {
                unlink($file['tmp_name']);
            }
        }
    }

    /**
     * Retrieve and prepare the files array from the event form.
     */
    private function getFiles(Form\Event\Event $form)
    {
        // Ensure data is available by running the validation process - does nothing if already run.
        $form->isValid();
        $files = $form->getData()['attachments']['files'];
        if (isset($files[0]) && $files[0]['error'] === UPLOAD_ERR_NO_FILE) {
            $files = [];
        }
        return $files;
    }

    /**
     * Clean up uploaded files and notify the user if the event form cannot be processed.
     */
    private function cleanUpAfterValidationFailure(Form\Event\Event $form)
    {
        $files = $this->getFiles($form);
        if (!empty($files)) {
            $this->deleteFiles($files);
            $message = 'Attachments were not uploaded due to validation errors on other inputs - ' .
                'please attach them again after addressing the validation messages.';
            $filesInput = $form->get('attachments')->get('files');
            $messages = $filesInput->getMessages();
            $messages[] = $message;
            $filesInput->setMessages($messages);
            $this->alert()->bad($message);
        }
    }

    public function newAction()
    {
        $this->layout()->title = 'Submit Event Proposal';
        $db = $this->db;

        $groups = $db->query(
            (new Sql($db))->buildSqlString(
                (new Select())
                    ->columns(['id', 'groupname', 'country'])
                    ->from('scagroup')
                    ->where(['status' => 'live'])
                    ->order('groupname')
            ),
            []
        )->toArray();
        $groupList = $this->arrayIndex($groups, 'id', 'groupname');

        $detailsForm = new Form\Event\Event($groupList);
        $viewModel = [
            'detailsForm' => $detailsForm,
            'groups'      => $groups,
        ];

        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $viewModel;
        }

        $detailsForm->setData(array_merge_recursive(
            $request->getPost()->toArray(),
            $request->getFiles()->toArray()
        ));
        if (!$detailsForm->isValid()) {
            $this->cleanUpAfterValidationFailure($detailsForm);
            return $viewModel;
        }

        // Form is valid - transform the values into those expected by the database.
        $rawValues = $detailsForm->getData();
        $values = $rawValues['eventGroup'] + $rawValues['stewardGroup'] + $rawValues['bookingGroup'];
        $files = $this->scanFilesWithAntiVirus($this->getFiles($detailsForm));

        // Save the event and attachment references to the database.
        try {
            $db->getDriver()->getConnection()->beginTransaction();

            $db->query(
                (new Sql($db))->buildSqlString(
                    (new Insert('events'))
                        ->values($values)
                ),
                $db::QUERY_MODE_EXECUTE
            );

            $eventId = $db->getDriver()->getConnection()->getLastGeneratedValue();

            foreach ($files as $file) {
                $this->insertAttachment($file, $eventId);
            }

            $db->getDriver()->getConnection()->commit();
        } catch (\Throwable $ex) {
            $db->getDriver()->getConnection()->rollback();

            // In case of error, delete the now-orphaned files.
            $this->deleteFiles($files);

            throw $ex;
        }
        $this->alert()->good("Successfully added event {$values['name']}.");

        if ($this->emailSteward($values, $groupList[$values['groupid']])) {
            $this->alert()->good('Notification email sent to steward.');
        } else {
            $this->alert()->bad('Failed to send notification email to steward.');
        }

        $seneschalResults = $db->query(
            (new Sql($db))->buildSqlString(
                (new Select())
                    ->columns([
                        'sca_name',
                        'email' => new Expression("CONCAT(offices.email, '@', scagroup.emailDomain)")
                    ])
                    ->from('warrants')
                    ->join(
                        'offices',
                        'offices.ID = warrants.office',
                        []
                    )
                    ->join(
                        'scagroup',
                        'scagroup.id = warrants.scagroup',
                        []
                    )
                    ->where([
                        'scagroup.id' => $values['groupid'],
                        'offices.ID IN (1, 18)',
                        '(warrants.start_date <= CURDATE() OR warrants.start_date IS NULL)',
                        '(warrants.end_date >= CURDATE() OR warrants.end_date IS NULL)',
                    ])
            ),
            []
        )->toArray();
        if (count($seneschalResults) !== 1) {
            $this->alert()->bad(
                'Unable to determine current group seneschal from Registry. Please contact them manually.'
            );
        } elseif ($this->emailSeneschal($seneschalResults[0])) {
            $this->alert()->good('Notification email sent to group seneschal.');
        } else {
            $this->alert()->bad(
                'Failed to send email to group seneschal. Please contact them manually.'
            );
        }

        return $viewModel;
    }

    public function listAction()
    {
        $this->layout()->title = 'Review Event Proposals';
        $db = $this->db;
        $authResponse = $this->auth()->ensureLevel(['admin', 'user']);
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
        $filterForm->get('groupid')->setAttribute('disabled', $this->auth()->getLevel() != 'admin');
        $viewModel = [
            'filterForm' => $filterForm,
            'events'     => [],
        ];

        $request = $this->getRequest();
        $queryData = $request->getQuery();
        $filterForm->setData([
            'groupid' => $this->auth()->getLevel() == 'admin'
                ? ($queryData['groupid'] ?: 'all')
                : $this->auth()->getId(),
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

    private function emailAnnounce($values, $hostGroup)
    {
        $variables = [
            'values'    => $values,
            'hostGroup' => $hostGroup,
        ];
        return $this->sendEmail(
            'announce@lochac.sca.org',
            $this->renderer->render(
                (new ViewModel($variables))
                    ->setTemplate('email/announceEventNotification/subject.phtml')
                    ->setTerminal(true)
            ),
            $this->renderer->render(
                (new ViewModel($variables))
                    ->setTemplate('email/announceEventNotification/body.phtml')
                    ->setTerminal(true)
            ),
            '"Lochac Events" <seneschaldb@lochac.sca.org>',
            true
        );
    }

    private function emailSecretary($values, $hostGroupName)
    {
        $mailTo = 'secretary@sca.org.nz';

        $mailSubj = 'Event over $5000 requiring insurance notification';

        $mailBody = "The steward has advised that this event will likely have more than $5,000 " .
                    "in income so please advise the insurance company to ensure coverage.\n\n" .
                    "EVENT DETAILS\n=============\n" .
                    "Event Name:\t" . $values['name'] . "\n" .
                    "Host Group:\t" . $hostGroupName . "\n";

        if ($values['startdate'] == $values['enddate']) {
            $mailBody .= "Date:\t\t" . date('l, F jS Y', strtotime($values['startdate'])) . "\n";
        } else {
            $mailBody .= "Start date:\t" . date('l, F jS Y', strtotime($values['startdate'])) . "\n" .
                         "End date:\t" . date('l, F jS Y', strtotime($values['enddate'])) . "\n";
        }
        if (!empty($values['timetable'])) {
            $mailBody .= "Timetable:\n" . $values['timetable'] . "\n";
        }
        if (!empty($values['website'])) {
            $mailBody .= "Website:\t" . $values['website'] . "\n";
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
                     "DESCRIPTION\n===========\n" . $values['description'] . "\n\n" .
                     "Kind regards,\nThe Lochac Seneschals' Database";

        return $this->sendEmail($mailTo, $mailSubj, $mailBody);
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

        if (!empty($values['timetable'])) {
            $mailBody .= "Timetable: {$values['timetable']}. ";
        }
        if (!empty($values['website'])) {
            $mailBody .= "Website: {$values['website']}. ";
        }

        $mailBody .= "{$values['description']} Steward: {$values['stewardname']}, {$values['stewardemail']}. ";

        if (empty($values['bookingcontact']) || empty($values['bookingsclose'])) {
            $mailBody .= "Bookings not required.\n\n";
        } else {
            $mailBody .= "Bookings: {$values['bookingcontact']} by " .
                         date('l, F jS Y', strtotime($values['bookingsclose'])) . "\n\n";
        }

        $mailBody .= "Participants are reminded that if they are unwell or showing cold or " .
                     "flu-like symptoms, they must not attend.\n\n";

        if ($hostGroup['state'] === 'VIC') {
            $mailBody .= "As this event is in the state of Victoria, please remember that anyone " .
                         "carrying or using any kind of sword in the state - including visitors - " .
                         "must carry proof that they completed the Victorian weapons exemption " .
                         "application process with the SCA Ltd Registrar - see " .
                         "https://sca.org.au/victorian-weapons-legislation/ for detailed information.\n\n";
        }

        $mailBody .= "Kind regards,\nThe Lochac Seneschals' Database";

        return $this->sendEmail($mailTo, $mailSubj, $mailBody);
    }

    private function getGoogleCalendarService(): Calendar
    {
        $client = new Google\Client();
        $client->setAuthConfig('google-key.json');
        $client->setScopes([Calendar::CALENDAR]);

        // Is this needed?
        // if ($client->isAccessTokenExpired()) {
        //     $client->fetchAccessTokenWithAssertion();
        // }

        return new Calendar($client);
    }

    private function updateCalendar($values, $hostGroup, $eventId)
    {
        try {
            $service = $this->getGoogleCalendarService();

            if (empty($eventId)) {
                $event = new Calendar\Event();
            } else {
                $event = $service->events->get($this->googleCalendarId, $eventId);
            }

            $event->summary = $values['name'] . " (" . $hostGroup['groupname'] . ")";
            $event->location = $values['location'];
            $event->description = "Steward:\t" . $values['stewardname'] . "\n"
                                . "Email:\t\t" . $values['stewardemail'] . "\n";
            if (!empty($values['website'])) {
                $event->description .= "Website:\t{$values['website']}\n";
            }
            $event->description .= "\n" . $values['description'];

            if ($hostGroup['state'] === 'VIC') {
                $event->description .= "\n\nAs this event is in the state of Victoria, please remember that anyone " .
                                       "carrying or using any kind of sword in the state - including visitors - " .
                                       "must carry proof that they completed the Victorian weapons exemption " .
                                       "application process with the SCA Ltd Registrar - see " .
                                       "https://sca.org.au/victorian-weapons-legislation/ for detailed " .
                                       "information.";
            }

            $event->start = ['date' => $values['startdate']];
            // Google uses exclusive end dates, so we add a day to the end date
            $event->end = ['date' => date('Y-m-d', strtotime($values['enddate']) + 60 * 60 * 24)];

            if (empty($eventId)) {
                $event = $service->events->insert($this->googleCalendarId, $event);
            } else {
                $event = $service->events->update($this->googleCalendarId, $eventId, $event);
            }

            return $event->id;
        } catch (Google\Service\Exception $e) {
            $this->alert()->bad('GCal error: ' . $e->getMessage());
            return false;
        }
    }

    private function deleteCalendar($eventId)
    {
        if (empty($eventId)) {
            return false;
        }

        try {
            $service = $this->getGoogleCalendarService();

            $service->events->delete($this->googleCalendarId, $eventId);

            return true;
        } catch (Google\Service\Exception $e) {
            $this->alert()->bad('GCal error: ' . $e->getMessage());
            return false;
        }
    }

    public function editAction()
    {
        $this->layout()->title = 'Edit Event Proposal';
        $db = $this->db;
        $authResponse = $this->auth()->ensureLevel(['admin', 'user']);
        if ($authResponse) {
            return $authResponse;
        }

        $groups = $db->query(
            (new Sql($db))->buildSqlString(
                (new Select())
                    ->columns(['id', 'groupname', 'country'])
                    ->from('scagroup')
                    ->where(['status' => 'live'])
                    ->order('groupname')
            ),
            []
        )->toArray();
        $groupList = $this->arrayIndex($groups, 'id', 'groupname');

                                                            //----------------------------------------------------------
                                                            // Check that the eventid provided exists and
                                                            // that the user is allowed to access it.
                                                            //----------------------------------------------------------
        $request = $this->getRequest();
        $eventId = $request->getQuery()['eventid'];
        if (!is_numeric($eventId)) {
            return $this->notFoundAction();
        }
        $eventId = (int) $eventId;
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
        if ($this->auth()->getLevel() != 'admin' && $this->auth()->getId() != $initialData['groupid']) {
            return $this->notFoundAction();
        }

        // Retrieve the details of any attachments for this event.
        $rawAttachments = $db->query(
            (new Sql($db))->buildSqlString(
                (new Select())
                    ->from('event_attachment')
                    ->where([
                        'event_id' => $eventId,
                        'deleted'  => 0,
                    ])
            ),
            []
        )->toArray();
        $attachments = [];
        foreach ($rawAttachments as $rawAttachment) {
            $attachment = (array) $rawAttachment;
            $attachment['downloadLink'] = $this->url()->fromRoute(
                'event/attachment/download',
                ['id' => $attachment['id']]
            );
            $attachment['deleteLink'] = $this->url()->fromRoute(
                'event/attachment/delete',
                ['id' => $attachment['id']],
                ['query' => ['redirectUrl' => $this->currentUrl()]]
            );
            $attachments[] = $attachment;
        }

                                                            //----------------------------------------------------------
                                                            // Build details form
                                                            //----------------------------------------------------------
        $detailsForm = new Form\Event\Event($groupList, true, $attachments);
        $detailsForm->setData([
            'eventGroup' => array_intersect_key($initialData, array_flip([
                'name',
                'groupid',
                'startdate',
                'enddate',
                'timetable',
                'location',
                'type',
                'description',
                'price',
                'website',
                'notifyInsurer',
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
            'groups'      => $groups,
        ];

        if (!$request->isPost()) {
            return $viewModel;
        }

                                                            //----------------------------------------------------------
                                                            // Process event form
                                                            //----------------------------------------------------------
        $detailsForm->setData(array_merge_recursive(
            $request->getPost()->toArray(),
            $request->getFiles()->toArray()
        ));
        if (!$detailsForm->isValid()) {
            $this->cleanUpAfterValidationFailure($detailsForm);
            return $viewModel;
        }

        $rawValues = $detailsForm->getData();
        $values = $rawValues['eventGroup'] + $rawValues['stewardGroup'] + $rawValues['bookingGroup'];
        $values['status'] = $rawValues['submitGroup']['status'];
        $sendTo = $rawValues['submitGroup']['sendto'] ?: [];
        $files = $this->scanFilesWithAntiVirus($this->getFiles($detailsForm));

        // Save the event and attachment references to the database.
        try {
            $db->getDriver()->getConnection()->beginTransaction();

            $db->query(
                (new Sql($db))->buildSqlString(
                    (new Update('events'))
                        ->set($values)
                        ->where(['eventid' => $eventId])
                ),
                $db::QUERY_MODE_EXECUTE
            );

            foreach ($files as $file) {
                $this->insertAttachment($file, $eventId);
            }

            $db->getDriver()->getConnection()->commit();
        } catch (\Throwable $ex) {
            $db->getDriver()->getConnection()->rollback();

            // In case of error, delete the now-orphaned files.
            $this->deleteFiles($files);

            throw $ex;
        }
        $this->alert()->good("Successfully updated event {$values['name']} in database.");
        if (!empty($files)) {
            $refreshUrl = $this->currentUrl();
            $this->alert()->good(
                "New attachments uploaded, but not visible below - <a href='{$refreshUrl}'>click to refresh</a>."
            );
        }

        $hostGroup = (array) $db->query(
            (new Sql($db))->buildSqlString(
                (new Select())
                    ->columns(['groupname', 'type', 'state'])
                    ->from('scagroup')
                    ->where(['id' => $values['groupid']])
            ),
            []
        )->toArray()[0];

                                                            //----------------------------------------------------------
                                                            // Email the steward
                                                            //----------------------------------------------------------
        if ($this->emailSteward($values, $groupList[$values['groupid']])) {
            $this->alert()->good('Notification email sent to steward.');
        } else {
            $this->alert()->bad('Failed to send notification email to steward.');
        }

                                                            //----------------------------------------------------------
                                                            // If event approved and Pegasus selected, send to Pegasus
                                                            //----------------------------------------------------------
        if (in_array('pegasus', $sendTo) && $values['status'] == 'approved') {
            if ($this->emailPegasus($values, $hostGroup)) {
                $this->alert()->good('Event submitted to Pegasus.');
            } else {
                $this->alert()->bad('Failed to submit event to Pegasus.');
            }
        }

                                                            //----------------------------------------------------------
                                                            // If event not approved, make sure it isn't on the calendar
                                                            //----------------------------------------------------------
        if ($values['status'] != 'approved') {
            if (!empty($initialData['googleid'])) {
                $result = $this->deleteCalendar($initialData['googleid']);

                if ($result === false) {
                    $this->alert()->bad('Failed to remove event from Kingdom Calendar.');
                } else {
                    $this->alert()->good('Removed event from Kingdom Calendar.');

                    // store updated googleId
                    $db->query(
                        (new Sql($db))->buildSqlString(
                            (new Update('events'))
                                ->set(['googleid' => null])
                                ->where(['eventid' => $eventId])
                        ),
                        $db::QUERY_MODE_EXECUTE
                    );
                    $this->alert()->good('Removed GCal event ID from database.');
                }
            }
                                                            //----------------------------------------------------------
                                                            // If event approved and calendar selected, add to calendar
                                                            //----------------------------------------------------------
        } else {
            if (in_array('calendar', $sendTo)) {
                $result = $this->updateCalendar(
                    $values,
                    $hostGroup,
                    isset($initialData['googleid']) ? $initialData['googleid'] : null
                );

                if ($result === false) {
                    $this->alert()->bad('Failed to update Kingdom Calendar.');
                } else {
                    $this->alert()->good('Updated Kingdom Calendar.');

                    // store updated googleId
                    $db->query(
                        (new Sql($db))->buildSqlString(
                            (new Update('events'))
                                ->set(['googleid' => $result])
                                ->where(['eventid' => $eventId])
                        ),
                        $db::QUERY_MODE_EXECUTE
                    );
                    $this->alert()->good('Stored GCal event ID in database.');
                }
            }
        }

                                                            //----------------------------------------------------------
                                                            // If event approved and Announce selected, send to Announce
                                                            //----------------------------------------------------------
        if (in_array('announce', $sendTo) && $values['status'] == 'approved') {
            if ($this->emailAnnounce($values, $hostGroup)) {
                $this->alert()->good('Notification email sent to Lochac-Announce.');
            } else {
                $this->alert()->bad('Failed to send notification email to Lochac-Announce.');
            }
        }

                                                            //----------------------------------------------------------
                                                            // If event approved and notifyInsurer selected, send to
                                                            // the secretary.
                                                            //----------------------------------------------------------
        if ($values['notifyInsurer'] && $values['status'] == 'approved') {
            if ($this->emailSecretary($values, $groupList[$values['groupid']])) {
                $this->alert()->good('Notification email sent to SCA NZ Secretary.');
            } else {
                $this->alert()->bad('Failed to send notification email to SCA NZ Secretary.');
            }
        }

        return $viewModel;
    }

    public function downloadAttachmentAction()
    {
        $db = $this->db;
        $authResponse = $this->auth()->ensureLevel(['admin', 'user']);
        if ($authResponse) {
            return $authResponse;
        }

                                                            //----------------------------------------------------------
                                                            // Check that the attachment specified exists and
                                                            // that the user is allowed to access it.
                                                            //----------------------------------------------------------
        $attachmentId = $this->params('id');
        if (!is_numeric($attachmentId)) {
            return $this->notFoundAction();
        }
        $attachmentQuery = $db->query(
            (new Sql($db))->buildSqlString(
                (new Select())
                    ->columns([
                        'location',
                        'name',
                    ])
                    ->from('event_attachment')
                    ->join('events', 'events.eventid = event_attachment.event_id', ['groupid'])
                    ->where([
                        'event_attachment.id'      => $attachmentId,
                        'event_attachment.deleted' => 0,
                    ])
            ),
            []
        )->toArray();
        if (count($attachmentQuery) == 0) {
            return $this->notFoundAction();
        }
        $attachment = (array) $attachmentQuery[0];
        if ($this->auth()->getLevel() != 'admin' && $this->auth()->getId() != $attachment['groupid']) {
            return $this->notFoundAction();
        }
        if (!file_exists($attachment['location'])) {
            return $this->notFoundAction();
        }

        // Serve the download.
        $response = $this->getResponse();
        $response->getHeaders()->addHeaders([
            'X-Sendfile'          => realpath($attachment['location']),
            'Content-Type'        => 'application/octet-stream',
            'Content-Disposition' => "attachment; filename=\"{$attachment['name']}\"",
        ]);
        return $response;
    }

    public function deleteAttachmentAction()
    {
        $this->layout()->title = 'Delete Attachment';
        $db = $this->db;
        $authResponse = $this->auth()->ensureLevel(['admin', 'user']);
        if ($authResponse) {
            return $authResponse;
        }

                                                            //----------------------------------------------------------
                                                            // Check that the attachment specified exists and
                                                            // that the user is allowed to access it.
                                                            //----------------------------------------------------------
        $attachmentId = $this->params('id');
        if (!is_numeric($attachmentId)) {
            return $this->notFoundAction();
        }
        $attachmentQuery = $db->query(
            (new Sql($db))->buildSqlString(
                (new Select())
                    ->columns(['name'])
                    ->from('event_attachment')
                    ->join('events', 'events.eventid = event_attachment.event_id', ['groupid', 'event_name' => 'name'])
                    ->where([
                        'event_attachment.id'      => $attachmentId,
                        'event_attachment.deleted' => 0,
                    ])
            ),
            []
        )->toArray();
        if (count($attachmentQuery) == 0) {
            return $this->notFoundAction();
        }
        $attachment = (array) $attachmentQuery[0];
        if ($this->auth()->getLevel() != 'admin' && $this->auth()->getId() != $attachment['groupid']) {
            return $this->notFoundAction();
        }

        // User has access - show them a confirmation form.
        $request = $this->getRequest();
        $redirectUrl = isset($request->getQuery()['redirectUrl']) ? $request->getQuery()['redirectUrl'] : '';
        $form = new Form\Event\DeleteAttachment($redirectUrl);
        $viewModel = [
            'attachment' => $attachment,
            'deleteForm' => $form,
        ];

        if (!$request->isPost()) {
            return $viewModel;
        }

        $form->setData($request->getPost());
        if (!$form->isValid()) {
            return $viewModel;
        }

        if ($form->getData()['submit']) {
            // User has confirmed - mark the attachment as deleted.
            $db->query(
                (new Sql($db))->buildSqlString(
                    (new Update('event_attachment'))
                        ->set(['deleted' => 1])
                        ->where(['id' => $attachmentId])
                ),
                $db::QUERY_MODE_EXECUTE
            );
        }

        // Ensure redirect URL is valid and relative, i.e. not hijacking the user to a different site.
        $redirectUrl = $form->getData()['redirectUrl'];
        $uri = new Uri($redirectUrl);
        if (!$uri->isValid() || $uri->getHost() != null) {
            $redirectUrl = '';
        }

        // If the redirect URL is not given or is not valid, redirect to the home page.
        if (empty($redirectUrl)) {
            return $this->redirect()->toRoute('home');
        }
        // Otherwise, redirect to that URL.
        return $this->redirect()->toUrl($redirectUrl);
    }
}
