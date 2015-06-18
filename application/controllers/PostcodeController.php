<?php

class PostcodeController extends SenDb_Controller
{
    public function indexAction()
    {
        $this->_forward('query');
    }

    public function listAction()
    {
        global $db;
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
        global $db;
        $this->view->title = 'Postcode Query';
        $this->view->showResults = false; // Default - will be changed if results are returned.

        $groupList = $db->fetchPairs('SELECT id, groupname FROM scagroup ORDER BY groupname');

                                                            //----------------------------------------------------------
                                                            // Build postcode query form
                                                            //----------------------------------------------------------
        $form = new Zend_Form;
        $form->setAction('#');
        $form->setMethod('post');

        $form->addElement(
            'checkbox',
            'printable',
            array(
                'label' => 'Printable Report?'
            )
        );

                                                            //----------------------------------------------------------
                                                            // Section - query by group
                                                            //----------------------------------------------------------
        $form->addElement(
            'select',
            'group',
            array(
                'label'        => 'Group name:',
                'multiOptions' => $groupList
            )
        );
        $form->addElement(
            'submit',
            'querybygroup',
            array(
                'label' => 'Submit'
            )
        );
        $form->addDisplayGroup(
            array(
                'group',
                'querybygroup'
            ),
            'byGroup',
            array('legend' => 'Search by Group')
        );

                                                            //----------------------------------------------------------
                                                            // Section - query by postcode
                                                            //----------------------------------------------------------
        $form->addElement(
            'text',
            'postcode',
            array(
                'label'      => 'Postcode:',
                'validators' => array(
                    array(
                        'stringLength',
                        false,
                        array(0, 4)
                    )
                )
            )
        );
        $form->addElement(
            'submit',
            'querybycode',
            array(
                'label' => 'Submit'
            )
        );
        $form->addDisplayGroup(
            array(
                'postcode',
                'querybycode'
            ),
            'byCode',
            array('legend' => 'Search by Postcode')
        );

                                                            //----------------------------------------------------------
                                                            // Section - query by postcode range
                                                            //----------------------------------------------------------
        $form->addElement(
            'text',
            'rangestart',
            array(
                'label'      => 'Range Start:',
                'validators' => array(
                    array(
                        'stringLength',
                        false,
                        array(0, 4)
                    )
                )
            )
        );
        $form->addElement(
            'text',
            'rangeend',
            array(
                'label'      => 'Range End:',
                'validators' => array(
                    array(
                        'stringLength',
                        false,
                        array(0, 4)
                    )
                )
            )
        );
        $form->addElement(
            'submit',
            'querybyrange',
            array(
                'label' => 'Submit'
            )
        );
        $form->addDisplayGroup(
            array(
                'rangestart',
                'rangeend',
                'querybyrange'
            ),
            'byRange',
            array('legend' => 'Search by Postcode Range')
        );

                                                            //----------------------------------------------------------
                                                            // Section - query by locality
                                                            //----------------------------------------------------------
        $form->addElement(
            'text',
            'locality',
            array(
                'label'      => 'Suburb/Locality Name:',
                'validators' => array(
                    array(
                        'stringLength',
                        false,
                        array(0,64)
                    )
                )
            )
        );
        $form->addElement(
            'submit',
            'querybylocality',
            array(
                'label' => 'Submit'
            )
        );
        $form->addDisplayGroup(
            array(
                'locality',
                'querybylocality'
            ),
            'byLocality',
            array('legend' => 'Search by Suburb Name')
        );

        $form->addElement(
            'submit',
            'reset',
            array(
                'label' => 'Reset'
            )
        );

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
                    $this->view->message .= "<div class='bad'>Possible error fetching data.</div><br />\n";

                }

                foreach ($results as $result) {
                    // Need to find groupname based on groupid.
                    $result->groupname = $groupList[$result->groupid];

                    // Also need a list of locality names with this postcode.
                    $sql = "SELECT locality FROM postcode WHERE postcode={$db->quote($result->postcode)} ORDER BY locality";
                    try {
                        $localities[$result->postcode] = $db->fetchCol($sql);

                    } catch (Exception $e) {
                        $this->view->message .= "<div class='bad'>Possible error getting suburb list for postcode " .
                                                                   "{$result->postcode}.</div><br />\n";
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
            $this->view->message .= "<div class='bad'>Form not valid.</div><br />\n";
            $this->view->showForm = true;
        }

        $this->view->form = $form;
    }

    public function assignAction()
    {
        $auth = authenticate();
        global $db;
        if($auth['level'] != 'admin') {
            throw new Exception('User not authorised for this task.');
            return;
        }

        $this->view->title = 'Assign Postcodes';
        $this->view->message = '';
        $groupList = $db->fetchPairs('SELECT id, groupname FROM scagroup ORDER BY groupname');

                                                            //----------------------------------------------------------
                                                            // Build postcode assignment form
                                                            //----------------------------------------------------------
        $form = new Zend_Form();
        $form->setAction('#');
        $form->setMethod('post');

        $form->addElement(
            'text',
            'rangestart',
            array(
                'label'      => 'Postcode Range Start:',
                'required'   => true,
                'filters'    => array('stringTrim'),
                'validators' => array(
                    'int',
                    array(
                        'stringLength',
                        false,
                        array(0,4)
                    )
                )
            )
        );
        $form->addElement(
            'text',
            'rangeend',
            array(
                'label'      => 'Range End:',
                'required'   => true,
                'filters'    => array('stringTrim'),
                'validators' => array(
                    'int',
                    array(
                        'stringLength',
                        false,
                        array(0,4)
                    )
                )
            )
        );
        $form->addElement(
            'select',
            'group',
            array(
                'label'        => 'Assign to:',
                'multiOptions' => $groupList
            )
        );
        $form->addElement(
            'submit',
            'submit',
            array(
                'label' => 'Submit'
            )
        );
        $form->addDisplayGroup(
            array(
                'rangestart',
                'rangeend',
                'group',
                'submit'
            ),
            'assign'
        );

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
                $this->view->message .= "<div class='bad'>Possible error updating postcodes.</div><br />\n";
            }

            $this->view->message .= "<div class='good'>$updateCount row(s) updated.</div><br />\n";

        } else {
            // Don't.
        }

        $this->view->form = $form;
    }

    public function uploadAction()
    {
        $auth = authenticate();
        if($auth['level'] != 'admin') {
            throw new Exception('User not authorised for this task.');
            return;
        }

        $this->view->title = 'Upload Postcodes File';
        $this->view->message = '';

        // Library includes.
        require_once('Zend/Filter/BaseName.php');
        require_once('Zend/Filter/StripTags.php');

                                                            //----------------------------------------------------------
                                                            // Build the upload form
                                                            //----------------------------------------------------------
        $form = new Zend_Form;
        $form->setAction('#');
        $form->setMethod('post');

        $form->addElement(
            'file',
            'userfile',
            array(
                'required'   => true,
                'validators' => array(
                    array(
                        'Size',
                        false,
                        2560000
                    ),
                    array(
                        'Extension',
                        false,
                        'csv'
                    )
                )
            )
        );
        $form->addElement(
            'submit',
            'submit',
            array(
                'label' => 'Submit'
            )
        );

        if($form->isValid($_POST)) {
            // Process uploaded file
            global $db;
            $dir='/var/tmp';

            $filter = new Zend_Filter();

            $userfile = $_FILES['userfile'];
            $userfile_name = $filter->addFilter(new Zend_Filter_BaseName())
                                    ->addFilter(new Zend_Filter_StripTags())
                                    ->filter($userfile['name']);
            $targetfile = $dir . '/' . $userfile_name;

            if (move_uploaded_file($userfile['tmp_name'], $targetfile)) {
                $this->view->message .= "<div class='good'>File successfully uploaded.</div><br />\n";

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
                            $this->view->message .= "<div class='bad'>Possible error adding $row[Locality], " .
                                                                        "$row[Pcode], $row[State].</div><br />\n";
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
                            $this->view->message .= "<div class='bad'>Possible error updating $row[Locality], " .
                                                                        "$row[Pcode], $row[State].</div><br />\n";
                        }
                        $updateCount++;
                    }
                }

                // delete any entries not in the uploaded file
                try {
                    $deleteCount = $db->delete('postcode',"current='N'");

                } catch(Exception $e) {
                    $this->view->message .= "<div class='bad'>Possible error deleting old entries.</div><br />\n";
                }
                $this->view->message .= "$insertCount row(s) added.<br />\n" .
                                        "$updateCount row(s) updated.<br />\n" .
                                        "$deleteCount row(s) deleted.<br />\n";

                fclose($file);
            } else {
                $this->view->message .= "<div class='bad'>File move failed.</div><br />\n";
            }

        } else {
            // Display the form
            $this->view->form .= $form;
        }
    }

}

