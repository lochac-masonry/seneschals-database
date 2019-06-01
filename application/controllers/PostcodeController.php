<?php

class PostcodeController extends SenDb_Controller
{
    public function indexAction()
    {
        $this->_forward('query');
    }

    public function listAction()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer('echoMessage', null, true);

        $message .= "Postcode, (empty)Locality, State, Group Name<BR />\n";

        $sql = "SELECT DISTINCT a.postcode AS postcode, a.state AS state, " .
               "b.groupname AS groupname FROM postcode a JOIN scagroup b " .
               "ON a.groupid=b.id ORDER BY a.postcode, a.state";
        $db->setFetchMode(Zend_Db::FETCH_OBJ);

        try {
            $results = $db->fetchAll($sql);
        } catch(Exception $e) {
            die('Database error: ' . $e->getMessage);
        }

        foreach($results as $row) {
            $message .= $row->postcode . ",," . $row->state . "," .
                        $row->groupname . "<BR />\n";
        }

        $this->view->message = $message;
    }

    public function queryAction()
    {
        $db = Zend_Db_Table::getDefaultAdapter();
        $this->view->title = 'Postcode Query';
        $this->view->showResults = false; // Default - will be changed if results are returned.

        $groupList = $db->fetchPairs('SELECT id, groupname FROM scagroup ORDER BY groupname');

                                                            //----------------------------------------------------------
                                                            // Build postcode query form
                                                            //----------------------------------------------------------
        $form = new SenDb_Form_PostCode_Query(array('method' => 'post'));
        $form->group->options = $groupList;

                                                            //----------------------------------------------------------
                                                            // Process the form
                                                            //----------------------------------------------------------
        if($form->isValid($_POST)) {
            $values = $form->getValues();

            if($form->printable->isChecked()) {
                $this->_helper->layout->disableLayout();
                $this->_helper->viewRenderer('queryTable');
            }

            if($form->reset->isChecked()) {
                $form->reset();
            }

            $queryExists = true;
            if($form->querybygroup->isChecked()) {
                // Get listing from database where groupid as given.
                $sql = "SELECT DISTINCT postcode, state, groupid FROM postcode " .
                       "WHERE groupid={$db->quote($values['group'],Zend_Db::INT_TYPE)}";
            } elseif($form->querybycode->isChecked()) {
                // Get listing for postcode given
                $sql = "SELECT DISTINCT postcode, state, groupid FROM postcode " .
                       "WHERE postcode={$db->quote($values['postcode'],Zend_Db::INT_TYPE)}";
            } elseif($form->querybyrange->isChecked()) {
                // Get listing for all codes in range
                $sql = "SELECT DISTINCT postcode, state, groupid FROM postcode " .
                       "WHERE postcode>={$db->quote($values['rangestart'],Zend_Db::INT_TYPE)} " .
                       "AND postcode<={$db->quote($values['rangeend'],Zend_Db::INT_TYPE)}";
            } elseif($form->querybylocality->isChecked()) {
                // Get listing for a given locality
                $sql = "SELECT DISTINCT postcode, state, groupid FROM postcode " .
                       "WHERE locality LIKE {$db->quote('%' . $values['locality'] . '%')}";
            } else { // The user didn't ask for anything - better show the form.
                $queryExists = false;
                $this->view->showForm = true;
            }

            if($queryExists) {
                $db->setFetchMode(Zend_Db::FETCH_OBJ);
                try {
                    $results = $db->fetchAll($sql);

                } catch (Exception $e) {
                    $this->addAlert('Possible error fetching data.', SenDb_Controller::ALERT_BAD);

                }

                foreach ($results as $result) {
                    // Need to find groupname based on groupid.
                    $result->groupname = $groupList[$result->groupid];

                    // Also need a list of locality names with this postcode.
                    $sql = "SELECT locality FROM postcode WHERE postcode={$db->quote($result->postcode)} ORDER BY locality";
                    try {
                        $localities[$result->postcode] = $db->fetchCol($sql);

                    } catch (Exception $e) {
                        $this->addAlert('Possible error getting suburb list for postcode ' . $result->postcode . '.', SenDb_Controller::ALERT_BAD);
                    }

                    $result->localities = '';
                    foreach ($localities[$result->postcode] as $locality) {
                        if($result->localities == '') {
                            $result->localities = $locality;
                        } else {
                            $result->localities .= ', ' . $locality;
                        }
                    }
                }
                $this->view->results = $results;
                $this->view->showResults = true;
            }
        } else {
            $this->addAlert('Form not valid.', SenDb_Controller::ALERT_BAD);
            $this->view->showForm = true;
        }

        $this->view->form = $form;
    }

    public function assignAction()
    {
        $auth = authenticate();
        $db = Zend_Db_Table::getDefaultAdapter();
        if($auth['level'] != 'admin') {
            throw new SenDb_Exception_NotAuthorised();
            return;
        }

        $this->view->title = 'Assign Postcodes';
        $groupList = $db->fetchPairs('SELECT id, groupname FROM scagroup ORDER BY groupname');

                                                            //----------------------------------------------------------
                                                            // Build postcode assignment form
                                                            //----------------------------------------------------------
        $form = new SenDb_Form_PostCode_Assign(array('method' => 'post'));
        $form->group->options = $groupList;

                                                            //----------------------------------------------------------
                                                            // Process form - attempt to assign postcode range
                                                            //----------------------------------------------------------
        if($form->isValid($_POST)) {
            $values = $form->getValues();

            try {
                $updateCount = $db->update(
                    'postcode',
                    array('groupid' => $values['group']),
                    array( // where
                        "postcode>={$db->quote($values['rangestart'],Zend_Db::INT_TYPE)}",
                        "postcode<={$db->quote($values['rangeend'],Zend_Db::INT_TYPE)}"
                    )
                );

            } catch(Exception $e) {
                $this->addAlert('Possible error updating postcodes.', SenDb_Controller::ALERT_BAD);
            }

            $this->addAlert($updateCount . ' row(s) updated.', SenDb_Controller::ALERT_GOOD);

        } else {
            // Don't.
        }

        $this->view->form = $form;
    }

    public function uploadAction()
    {
        $auth = authenticate();
        if($auth['level'] != 'admin') {
            throw new SenDb_Exception_NotAuthorised();
            return;
        }

        $this->view->title = 'Upload Postcodes File';

                                                            //----------------------------------------------------------
                                                            // Build the upload form
                                                            //----------------------------------------------------------
        $form = new SenDb_Form_PostCode_Upload(array('method' => 'post'));

        if($form->isValid($_POST)) {
            // Process uploaded file
            $db = Zend_Db_Table::getDefaultAdapter();
            $dir='/var/tmp';

            $filter = new Zend_Filter();

            $userfile = $_FILES['userfile'];
            $userfile_name = $filter->addFilter(new Zend_Filter_BaseName())
                                    ->addFilter(new Zend_Filter_StripTags())
                                    ->filter($userfile['name']);
            $targetfile = $dir . '/' . $userfile_name;

            if (move_uploaded_file($userfile['tmp_name'], $targetfile)) {
                $this->addAlert('File successfully uploaded.', SenDb_Controller::ALERT_GOOD);

                // Mark all of the existing postcode records as old, and initialise counters.
                $db->update('postcode',array('current' => 'N'));
                $updateCount = 0;
                $insertCount = 0;
                $deleteCount = 0;

                // We have a CSV file at targetfile - open it.
                $file = fopen($targetfile,'r');

                // Grab the first row to use as headings.
                if(!feof($file)) {
                    $headRow = fgetcsv($file, 0, ',', '"');
                }

                // Get each row in turn
                while(!feof($file)) {
                    $rowData = fgetcsv($file, 0, ',', '"');

                    // add header row as keys for row array
                    foreach($rowData as $key => $value) {
                        $row[$headRow[$key]] = $value;
                    }

                    $sql = "SELECT COUNT(*) FROM postcode WHERE postcode={$db->quote($row['Pcode'],Zend_Db::INT_TYPE)} " .
                           "AND locality={$db->quote($row['Locality'])} AND state={$db->quote($row['State'])}";
                    $exists = $db->fetchOne($sql);

                    // Does the entry exist?
                    if($exists === 0) {
                        // Find the group that has the postcode and add to db.
                        $sql = "SELECT groupid FROM postcode WHERE postcode=$row[Pcode]";
                        $groupID = $db->fetchOne($sql);
                        if($groupID == 0) {
                            $groupID = 1;
                        }

                        try {
                            $db->insert(
                                'postcode',
                                array(
                                    'current'          => 'Y',
                                    'comments'         => $row['Comments'],
                                    'deliveryoffice'   => $row['DeliveryOffice'],
                                    'presortindicator' => $row['PresortIndicator'],
                                    'parcelzone'       => $row['ParcelZone'],
                                    'bspnumber'        => $row['BSPnumber'],
                                    'bspname'          => $row['BSPname'],
                                    'category'         => $row['Category'],
                                    'postcode'         => $row['Pcode'],
                                    'locality'         => $row['Locality'],
                                    'state'            => $row['State'],
                                    'groupid'          => $groupID
                                )
                            );
                        } catch(Exception $e) {
                            $this->addAlert('Possible error adding ' . $row[Locality] . ', ' . $row[Pcode] . ', ' . $row[State] . '.', SenDb_Controller::ALERT_BAD);
                        }
                        $insertCount++;
                    } else {
                        // Update with current details.
                        try {
                            $db->update(
                                'postcode',
                                array( // set
                                    'current'          => 'Y',
                                    'comments'         => $row['Comments'],
                                    'deliveryoffice'   => $row['DeliveryOffice'],
                                    'presortindicator' => $row['PresortIndicator'],
                                    'parcelzone'       => $row['ParcelZone'],
                                    'bspnumber'        => $row['BSPnumber'],
                                    'bspname'          => $row['BSPname'],
                                    'category'         => $row['Category']
                                ),
                                array( // where
                                    "postcode={$db->quote($row['Pcode'],Zend_Db::INT_TYPE)}",
                                    "locality={$db->quote($row['Locality'])}",
                                    "state={$db->quote($row['State'])}"
                                )
                            );
                        } catch(Exception $e) {
                            $this->addAlert('Possible error updating ' . $row[Locality] . ', ' . $row[Pcode] . ', ' . $row[State] . '.', SenDb_Controller::ALERT_BAD);
                        }
                        $updateCount++;
                    }
                }

                // delete any entries not in the uploaded file
                try {
                    $deleteCount = $db->delete('postcode',"current='N'");

                } catch(Exception $e) {
                    $this->addAlert('Possible error deleting old entries.', SenDb_Controller::ALERT_BAD);
                }
                $this->addAlert($insertCount . ' row(s) added.');
                $this->addAlert($updateCount . ' row(s) updated.');
                $this->addAlert($deleteCount . ' row(s) deleted.');

                fclose($file);
            } else {
                $this->addAlert('File move failed.', SenDb_Controller::ALERT_BAD);
            }

        } else {
            // Display the form
            $this->view->form .= $form;
        }
    }

}

