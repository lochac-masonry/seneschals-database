<?php

class ReportController extends SenDb_Controller
{
    public function indexAction()
    {
        $auth = authenticate();
        global $db;
        if($auth['level'] != 'admin' && $auth['level'] != 'user') {
            throw new SenDb_Exception_NotAuthorised();
            return;
        }

        $this->_helper->viewRenderer('echoMessage',null,true);
        $this->view->title = 'Submit Quarterly Report';
        $this->view->message = '';
        $groupList = $db->fetchPairs('SELECT id, groupname FROM scagroup ORDER BY groupname');

                                                            //----------------------------------------------------------
                                                            // Group selection form
                                                            // Enabled for admin, disabled for regular groups
                                                            //----------------------------------------------------------
        $groupSelectForm = new SenDb_Form_GroupSelect(array('method' => 'get'));
        $groupSelectForm->groupid->options = $groupList;

        if($auth['level'] != 'admin') {
            $groupSelectForm->groupid->disabled = true;
            $groupSelectForm->submit->disabled = true;
        }

        if($groupSelectForm->isValid($_GET)) {
            //Show relevant details for the selected group.
            if($auth['level'] == 'admin') {
                $values['id'] = $groupSelectForm->getValue('groupid');
            } else {
                $values['id'] = $auth['id'];
            }
                                                            //----------------------------------------------------------
                                                            // Build the report form
                                                            //----------------------------------------------------------
            $detailsForm = new SenDb_Form_Report(array('method' => 'post'));
            $detailsForm->parentid->options = $groupList;

                                                            //----------------------------------------------------------
                                                            // Section - subgroups, if any
                                                            //----------------------------------------------------------
            $db->setFetchMode(Zend_Db::FETCH_OBJ);
            $subgroups = $db->fetchAll("SELECT id, type, groupname FROM scagroup WHERE parentid={$db->quote($values['id'],Zend_Db::INT_TYPE)} " .
                                       "AND (status='live' OR status='proposed')");
            foreach($subgroups as $subgroup) {
                $detailsForm->addElement(
                    'textarea',
                    'subgroup'.$subgroup->id,
                    array(
                        'label' => $subgroup->type . ' of ' . $subgroup->groupname,
                        'cols'  => 50,
                        'rows'  => 12,
                        'wrap'  => 'virtual'
                    )
                );

                $subgroupFields[] = 'subgroup'.$subgroup->id;
            }
            if(!empty($subgroupFields)) {
                $detailsForm->addDisplayGroup(
                    $subgroupFields,
                    'subgroups',
                    array('legend' => 'Subgroups')
                );
            }

            $detailsForm->addElement(
                'submit',
                'submit',
                array(
                    'label' => 'Submit'
                )
            );
            $detailsForm->addElement(
                'submit',
                'reset',
                array(
                    'label' => 'Reset'
                )
            );

                                                            //----------------------------------------------------------
                                                            // Process the submitted report
                                                            //----------------------------------------------------------
            if($detailsForm->isValid($_POST)) {
                $values = array_merge($values, $detailsForm->getValues());

                if($detailsForm->reset->isChecked()) {
                    $detailsForm->reset();

                } elseif($detailsForm->submit->isChecked()) {
                    // Fetch fixed information.
                    $db->setFetchMode(Zend_Db::FETCH_ASSOC);
                    $values = array_merge($values, $db->fetchRow("SELECT type, groupname, parentid FROM scagroup WHERE id=" .
                                                                 $db->quote($values['id'],Zend_Db::INT_TYPE)));

                                                            //----------------------------------------------------------
                                                            // Update database with latest group details
                                                            //----------------------------------------------------------
                    $values['lastreport'] = date('Y-m-d');
                    $keys = array(
                        'lastreport',
                        'scaname',
                        'realname',
                        'address',
                        'suburb',
                        'state',
                        'postcode',
                        'country',
                        'phone',
                        'email',
                        'website',
                        'memnum'
                    );

                    try {
                        $changed = $db->update(
                            'scagroup',
                            array_intersect_key($values,array_flip($keys)),
                            "id={$db->quote($values['id'],Zend_Db::INT_TYPE)}"
                        );

                    } catch(Exception $e) {
                        $this->addAlert('Possible error updating group details.', SenDb_Controller::ALERT_BAD);
                    }
                    $this->addAlert($changed . ' record(s) affected.');

                                                            //----------------------------------------------------------
                                                            // Compose report email
                                                            //----------------------------------------------------------
                    $mailsubj  = 'Report from the ' . $values['type'] . ' of ' . $values['groupname'];

                    $mailbody  = $mailsubj
                               . "\nDate: " . $values['lastreport']
                               . "\nSubmitted by: " . $values['scaname']
                               . " (" . $values['realname'] . ")"
                               . "\n"
                               . "\nSTATISTICS"
                               . "\n==========\n"
                               . $values['statistics']
                               . "\n"
                               . "\nREGULAR ACTIVITIES"
                               . "\n==================\n"
                               . $values['activities']
                               . "\n"
                               . "\nACHIEVEMENTS"
                               . "\n============\n"
                               . $values['achievements']
                               . "\n"
                               . "\nEVENTS"
                               . "\n======\n"
                               . $values['events']
                               . "\n"
                               . "\nPROBLEMS"
                               . "\n========\n"
                               . $values['problems']
                               . "\n"
                               . "\nQUESTIONS"
                               . "\n=========\n"
                               . $values['questions']
                               . "\n"
                               . "\nPLANS"
                               . "\n=====\n"
                               . $values['plans']
                               . "\n"
                               . "\nGENERAL COMMENTS"
                               . "\n================\n"
                               . $values['comments']
                               . "\n"
                               . "\nSUMMARY OF OFFICERS"
                               . "\n==================="
                               . "\n\n== Marshal\n" . $values['summarshal']
                               . "\n\n== Herald\n" . $values['sumherald']
                               . "\n\n== Arts and Sciences\n" . $values['sumartssci']
                               . "\n\n== Reeve\n" . $values['sumreeve']
                               . "\n\n== Constable\n" . $values['sumconstable']
                               . "\n\n== Chirurgeon\n" . $values['sumchirurgeon']
                               . "\n\n== Chronicler/Webminister\n" . $values['sumchronicler']
                               . "\n\n== Chatelaine/Hospitaller\n" . $values['sumchatelaine']
                               . "\n\n== Lists\n" . $values['sumlists']
                               . "\n"
                               . "\nSUMMARY OF SUB-GROUPS"
                               . "\n======================";

                    foreach($subgroups as $subgroup) {
                        $mailbody .= "\nSummary report for " . $subgroup->type . " of " . $subgroup->groupname;
                        $mailbody .= "\n" . $values['subgroup'.$subgroup->id] . "\n";
                    }

                    $mailto[] = $db->fetchOne("SELECT email FROM scagroup WHERE id={$db->quote($values['parentid'],Zend_Db::INT_TYPE)}");
                    $mailto[] = $values['email'];
                    if($values['copyhospit']) {
                        $mailto[] = "hospitaller@lochac.sca.org";
                    }
                    if($values['copychirurgeon']) {
                        $mailto[] = "chirurgeon@lochac.sca.org";
                    }
                    if(!empty($values['othercopy1'])) {
                        $mailto[] = $values['othercopy1'];
                    }
                    if(!empty($values['othercopy2'])) {
                        $mailto[] = $values['othercopy2'];
                    }

                    $mailheaders = "From: " . $values['email'];

                                                            //----------------------------------------------------------
                                                            // Send report
                                                            //----------------------------------------------------------
                    if(SenDb_Helper_Email::send($mailto,$mailsubj,$mailbody,$mailheaders)) {
                        $this->addAlert('Report sent to ' . count($mailto) . ' recipient(s).', SenDb_Controller::ALERT_GOOD);
                    } else {
                        $this->addAlert('Failed to send report.', SenDb_Controller::ALERT_BAD);
                    }

                }
            }
                                                            //----------------------------------------------------------
                                                            // Populate the form with current information
                                                            //----------------------------------------------------------
            if(!empty($values['id'])) {
                $db->setFetchMode(Zend_Db::FETCH_ASSOC);
                $defaults = $db->fetchRow("SELECT * FROM scagroup WHERE id={$db->quote($values['id'],Zend_Db::INT_TYPE)}");
                $detailsForm->setDefaults($defaults);
            }
        }

                                                            //----------------------------------------------------------
                                                            // Pre-select the user's group and render the form(s)
                                                            //----------------------------------------------------------
        if($auth['level'] == 'user') {
            $groupSelectForm->setDefaults(array('groupid' => $auth['id']));
        }

        $this->view->message .= "\n\n" . $groupSelectForm;
        if(!empty($detailsForm)) {
            $this->view->message .= "\n\n" . $detailsForm;
        }
    }

}

