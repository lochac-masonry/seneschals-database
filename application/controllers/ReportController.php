<?php

class ReportController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $auth = authenticate();
        global $db;
        if($auth['level'] != 'admin' && $auth['level'] != 'user') {
            throw new Exception('User not authorised for this task.');
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
        $groupSelectForm = new Zend_Form();
        $groupSelectForm->setAction('#');
        $groupSelectForm->setMethod('get');

        if($auth['level'] == 'admin') {
            $groupSelectForm->addElement(
                'select',
                'groupid',
                array(
                    'label'        => 'Select group (Lochac for misc. aliases):',
                    'multiOptions' => $groupList,
                    'validators'   => array('digits'),
                    'required'     => true
                )
            );
            $groupSelectForm->addElement(
                'submit',
                'submit',
                array(
                    'label' => 'Select'
                )
            );
        } else {
            $groupSelectForm->addElement(
                'select',
                'groupid',
                array(
                    'label'        => 'Select Group (Lochac for misc. aliases):',
                    'multiOptions' => $groupList,
                    'disabled'     => true
                )
            );
            $groupSelectForm->addElement(
                'submit',
                'submit',
                array(
                    'label'    => 'Select',
                    'disabled' => true
                )
            );
        }

        $groupSelectForm->setDecorators(array('FormElements', 'Form'));
        $groupSelectForm->groupid->setDecorators(array('ViewHelper', 'Label'));
        $groupSelectForm->submit->setDecorators(array('ViewHelper'));

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
            $detailsForm = new Zend_Form();
            $detailsForm->setAction('#');
            $detailsForm->setMethod('post');

                                                            //----------------------------------------------------------
                                                            // Section - general group details
                                                            //----------------------------------------------------------
            $detailsForm->addElement(
                'text',
                'groupname',
                array(
                    'label'    => 'Name of Group',
                    'size'     => 50,
                    'disabled' => true
                )
            );
            $detailsForm->addElement(
                'text',
                'website',
                array(
                    'label' => 'Group Website',
                    'size'  => 50
                )
            );
            $detailsForm->addElement(
                'select',
                'type',
                array(
                    'label'        => 'Group Type',
                    'disabled'     => true,
                    'multiOptions' => array(
                        'Kingdom'      => 'Kingdom',
                        'Principality' => 'Principality',
                        'Barony'       => 'Barony',
                        'Shire'        => 'Shire',
                        'Canton'       => 'Canton',
                        'College'      => 'College'
                    )
                )
            );
            $detailsForm->addElement(
                'select',
                'parentid',
                array(
                    'label'        => 'Parent Group',
                    'disabled'     => true,
                    'multiOptions' => $groupList
                )
            );
            $detailsForm->addDisplayGroup(
                array(
                    'groupname',
                    'website',
                    'type',
                    'parentid'
                ),
                'groupDetails',
                array('legend' => 'Group Details')
            );

                                                            //----------------------------------------------------------
                                                            // Section - seneschal details
                                                            //----------------------------------------------------------
            $detailsForm->addElement(
                'text',
                'scaname',
                array(
                    'label'    => 'SCA Name',
                    'size'     => 50,
                    'required' => true
                )
            );
            $detailsForm->addElement(
                'text',
                'realname',
                array(
                    'label'    => 'Legal Name',
                    'size'     => 50,
                    'required' => true
                )
            );
            $detailsForm->addElement(
                'text',
                'address',
                array(
                    'label'    => 'Street Address',
                    'size'     => 50,
                    'required' => true
                )
            );
            $detailsForm->addElement(
                'text',
                'suburb',
                array(
                    'label' => 'Suburb / Town',
                    'size'  => 20
                )
            );
            $detailsForm->addElement(
                'select',
                'state',
                array(
                    'label'        => 'State',
                    'multiOptions' => array(
                        'ACT' => 'ACT',
                        'NSW' => 'NSW',
                        'NT'  => 'NT',
                        'QLD' => 'QLD',
                        'SA'  => 'SA',
                        'TAS' => 'TAS',
                        'VIC' => 'VIC',
                        'WA'  => 'WA',
                        'NZ'  => 'Not Applicable (NZ)'
                    )
                )
            );
            $detailsForm->addElement(
                'text',
                'postcode',
                array(
                    'label' => 'Postcode',
                    'size'  => 4
                )
            );
            $detailsForm->addElement(
                'select',
                'country',
                array(
                    'label'        => 'Country',
                    'multiOptions' => array(
                        'AU' => 'Australia',
                        'NZ' => 'New Zealand'
                    )
                )
            );
            $detailsForm->addElement(
                'text',
                'phone',
                array(
                    'label' => 'Phone',
                    'size'  => 15
                )
            );
            $detailsForm->addElement(
                'text',
                'email',
                array(
                    'label'      => 'Email Address - Published on group listing',
                    'size'       => 40,
                    'required'   => true,
                    'validators' => array('emailAddress')
                )
            );
            $detailsForm->addElement(
                'text',
                'memnum',
                array(
                    'label'      => 'Member Number',
                    'size'       => 6,
                    'required'   => true,
                    'validators' => array('digits')
                )
            );
            $detailsForm->addElement(
                'text',
                'warrantstart',
                array(
                    'label'    => 'Warrant Start (YYYY-MM-DD)',
                    'size'     => 10,
                    'disabled' => true
                )
            );
            $detailsForm->addElement(
                'text',
                'warrantend',
                array(
                    'label'    => 'Warrant End (YYYY-MM-DD)',
                    'size'     => 10,
                    'disabled' => true
                )
            );
            $detailsForm->addElement(
                'text',
                'lastreport',
                array(
                    'label'    => 'Last Report (YYYY-MM-DD)',
                    'size'     => 10,
                    'disabled' => true
                )
            );
            $detailsForm->addDisplayGroup(
                array(
                    'scaname',
                    'realname',
                    'address',
                    'suburb',
                    'state',
                    'postcode',
                    'country',
                    'phone',
                    'email',
                    'memnum',
                    'warrantstart',
                    'warrantend',
                    'lastreport'
                ),
                'senDetails',
                array('legend' => 'Seneschal Details')
            );

                                                            //----------------------------------------------------------
                                                            // Section - CC recipient selection
                                                            //----------------------------------------------------------
            $detailsForm->addElement(
                'checkbox',
                'copyhospit',
                array(
                    'label' => 'Kingdom Hospitaller'
                )
            );
            $detailsForm->addElement(
                'checkbox',
                'copychirurgeon',
                array(
                    'label' => 'Kingdom Chirurgeon'
                )
            );
            $detailsForm->addElement(
                'text',
                'othercopy1',
                array(
                    'label'      => 'Other Email',
                    'validators' => array('emailAddress')
                )
            );
            $detailsForm->addElement(
                'text',
                'othercopy2',
                array(
                    'label'      => 'Other Email',
                    'validators' => array('emailAddress')
                )
            );
            $detailsForm->addDisplayGroup(
                array(
                    'copyhospit',
                    'copychirurgeon',
                    'othercopy1',
                    'othercopy2'
                ),
                'copies',
                array('legend' => 'Forward copies to:')
            );

                                                            //----------------------------------------------------------
                                                            // Section - report content
                                                            //----------------------------------------------------------
            $detailsForm->addElement(
                'textarea',
                'statistics',
                array(
                    'label' => 'Statistics: Total and Active Members, Total Funds',
                    'cols'  => 50,
                    'rows'  => 2,
                    'wrap'  => 'virtual'
                )
            );
            $detailsForm->addElement(
                'textarea',
                'activities',
                array(
                    'label' => 'Summary of Regular Activities',
                    'cols'  => 50,
                    'rows'  => 10,
                    'wrap'  => 'virtual'
                )
            );
            $detailsForm->addElement(
                'textarea',
                'achievements',
                array(
                    'label' => 'Special Achievements and Ideas that Worked',
                    'cols'  => 50,
                    'rows'  => 10,
                    'wrap'  => 'virtual'
                )
            );
            $detailsForm->addElement(
                'textarea',
                'events',
                array(
                    'label' => 'Summary of Events',
                    'cols'  => 50,
                    'rows'  => 10,
                    'wrap'  => 'virtual'
                )
            );
            $detailsForm->addElement(
                'textarea',
                'problems',
                array(
                    'label' => 'Problems of Note',
                    'cols'  => 50,
                    'rows'  => 10,
                    'wrap'  => 'virtual'
                )
            );
            $detailsForm->addElement(
                'textarea',
                'questions',
                array(
                    'label' => 'Questions',
                    'cols'  => 50,
                    'rows'  => 10,
                    'wrap'  => 'virtual'
                )
            );
            $detailsForm->addElement(
                'textarea',
                'plans',
                array(
                    'label' => 'Plans for the Future, Ideas, etc',
                    'cols'  => 50,
                    'rows'  => 10,
                    'wrap'  => 'virtual'
                )
            );
            $detailsForm->addElement(
                'textarea',
                'comments',
                array(
                    'label' => 'General Comments',
                    'cols'  => 50,
                    'rows'  => 10,
                    'wrap'  => 'virtual'
                )
            );
            $detailsForm->addDisplayGroup(
                array(
                    'statistics',
                    'activities',
                    'achievements',
                    'events',
                    'problems',
                    'questions',
                    'plans',
                    'comments'
                ),
                'report',
                array('legend' => 'Report Details')
            );

                                                            //----------------------------------------------------------
                                                            // Section - officer reports
                                                            //----------------------------------------------------------
            $detailsForm->addElement(
                'textarea',
                'summarshal',
                array(
                    'label' => 'Marshal',
                    'cols'  => 50,
                    'rows'  => 10,
                    'wrap'  => 'virtual'
                )
            );
            $detailsForm->addElement(
                'textarea',
                'sumherald',
                array(
                    'label' => 'Herald',
                    'cols'  => 50,
                    'rows'  => 10,
                    'wrap'  => 'virtual'
                )
            );
            $detailsForm->addElement(
                'textarea',
                'sumartssci',
                array(
                    'label' => 'Arts and Sciences',
                    'cols'  => 50,
                    'rows'  => 10,
                    'wrap'  => 'virtual'
                )
            );
            $detailsForm->addElement(
                'textarea',
                'sumreeve',
                array(
                    'label' => 'Reeve',
                    'cols'  => 50,
                    'rows'  => 10,
                    'wrap'  => 'virtual'
                )
            );
            $detailsForm->addElement(
                'textarea',
                'sumconstable',
                array(
                    'label' => 'Constable',
                    'cols'  => 50,
                    'rows'  => 10,
                    'wrap'  => 'virtual'
                )
            );
            $detailsForm->addElement(
                'textarea',
                'sumchirurgeon',
                array(
                    'label' => 'Chirurgeon',
                    'cols'  => 50,
                    'rows'  => 10,
                    'wrap'  => 'virtual'
                )
            );
            $detailsForm->addElement(
                'textarea',
                'sumchronicler',
                array(
                    'label' => 'Chronicler and/or Webminister',
                    'cols'  => 50,
                    'rows'  => 10,
                    'wrap'  => 'virtual'
                )
            );
            $detailsForm->addElement(
                'textarea',
                'sumchatelaine',
                array(
                    'label' => 'Chatelaine/Hospitaller',
                    'cols'  => 50,
                    'rows'  => 10,
                    'wrap'  => 'virtual'
                )
            );
            $detailsForm->addElement(
                'textarea',
                'sumlists',
                array(
                    'label' => 'Lists',
                    'cols'  => 50,
                    'rows'  => 10,
                    'wrap'  => 'virtual'
                )
            );
            $detailsForm->addDisplayGroup(
                array(
                    'summarshal',
                    'sumherald',
                    'sumartssci',
                    'sumreeve',
                    'sumconstable',
                    'sumchirurgeon',
                    'sumchronicler',
                    'sumchatelaine',
                    'sumlists'
                ),
                'officers',
                array('legend' => 'Summary of Officer Reports')
            );

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
                        $this->view->message .= "<div class='bad'>Possible error updating group details.</div><br />\n";
                    }
                    $this->view->message .= $changed . " record(s) affected.<br />\n";

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
                                                            // Send report to each recipient
                                                            //----------------------------------------------------------
                    foreach($mailto as $address) {
                        if(mail($address,$mailsubj,$mailbody,$mailheaders)) {
                            $this->view->message .= "<div class='good'>Report sent to " . $address . "</div><br />\n";
                        } else {
                            $this->view->message .= "<div class='bad'>Report failed to send to " . $address . "</div><br />\n";
                        }
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

