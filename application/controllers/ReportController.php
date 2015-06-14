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

        $groupSelectForm = new Zend_Form();
        $groupSelectForm->setAction('#')
                        ->setMethod('get');

        if($auth['level'] == 'admin') {
            $groupSelectForm->addElement('select','groupid',array('label' => 'Select group (Lochac for misc. aliases):',
                                                                  'multiOptions' => $groupList,
                                                                  'validators' => array('digits'),
                                                                  'required' => true));
            $groupSelectForm->addElement('submit','submit',array('label' => 'Select'));
        } else {
            $groupSelectForm->addElement('select','groupid',array('label' => 'Select Group (Lochac for misc. aliases):',
                                                                  'multiOptions' => $groupList,
                                                                  'disabled' => true));
            $groupSelectForm->addElement('submit','submit',array('label' => 'Select',
                                                                 'disabled' => true));
        }
        $groupSelectForm->setDecorators(array('FormElements', 'Form'));
        $groupSelectForm->groupid->setDecorators(array('ViewHelper', 'Label'));
        $groupSelectForm->submit->setDecorators(array('ViewHelper'));

        if($groupSelectForm->isValid($_GET)) {
            //Show relevant details for the selected group.
            if($auth['level'] == 'admin') $values['id'] = $groupSelectForm->getValue('groupid');
            else $values['id'] = $auth['id'];

            $detailsForm = new Zend_Form();
            $detailsForm->setAction('#')
                        ->setMethod('post')
                        ->addElement('text','groupname',array('label' => 'Name of Group', 'size' => 50,
                                                              'disabled' => true))
                        ->addElement('text','website',array('label' => 'Group Website', 'size' => 50))
                        ->addElement('select','type',array('label' => 'Group Type', 'disabled' => true,
                                                           'multiOptions' => array('Kingdom' => 'Kingdom',
                                                                                   'Principality' => 'Principality',
                                                                                   'Barony' => 'Barony',
                                                                                   'Shire' => 'Shire',
                                                                                   'Canton' => 'Canton',
                                                                                   'College' => 'College')))
                        ->addElement('select','parentid',array('label' => 'Parent Group', 'disabled' => true,
                                                               'multiOptions' => $groupList))
                        ->addDisplayGroup(array('groupname','website','type','parentid'),
                                          'groupDetails',array('legend' => 'Group Details'))
                        ->addElement('text','scaname',array('label' => 'SCA Name', 'size' => 50,
                                                            'required' => true))
                        ->addElement('text','realname',array('label' => 'Legal Name', 'size' => 50,
                                                            'required' => true))
                        ->addElement('text','address',array('label' => 'Street Address', 'size' => 50,
                                                            'required' => true))
                        ->addElement('text','suburb',array('label' => 'Suburb / Town', 'size' => 20))
                        ->addElement('select','state',array('label' => 'State',
                                                            'multiOptions' => array('NSW' => 'NSW', 'VIC' => 'VIC', 'QLD' => 'QLD',
                                                                                    'SA' => 'SA', 'ACT' => 'ACT', 'WA' => 'WA',
                                                                                    'TAS' => 'TAS', 'NT' => 'NT', 'NZ' => 'Not Applicable (NZ)')))
                        ->addElement('text','postcode',array('label' => 'Postcode', 'size' => 4))
                        ->addElement('select','country',array('label' => 'Country',
                                                              'multiOptions' => array('AU' => 'Australia', 'NZ' => 'New Zealand')))
                        ->addElement('text','phone',array('label' => 'Phone', 'size' => 15))
                        ->addElement('text','email',array('label' => 'Email Address - Published on group listing', 'size' => 40,
                                                          'required' => true, 'validators' => array('emailAddress')))
                        ->addElement('text','memnum',array('label' => 'Member Number', 'size' => 6,
                                                           'required' => true, 'validators' => array('digits')))
                        ->addElement('text','warrantstart',array('label' => 'Warrant Start (YYYY-MM-DD)',
                                                                 'size' => 10, 'disabled' => true))
                        ->addElement('text','warrantend',array('label' => 'Warrant End (YYYY-MM-DD)',
                                                               'size' => 10, 'disabled' => true))
                        ->addElement('text','lastreport',array('label' => 'Last Report (YYYY-MM-DD)',
                                                               'size' => 10, 'disabled' => true))
                        ->addDisplayGroup(array('scaname','realname','address','suburb','state','postcode','country','phone','email','memnum',
                                                'warrantstart','warrantend','lastreport'),'senDetails',array('legend' => 'Seneschal Details'))
                        ->addElement('checkbox','copyhospit',array('label' => 'Kingdom Hospitaller'))
                        ->addElement('checkbox','copychirurgeon',array('label' => 'Kingdom Chirurgeon'))
                        ->addElement('text','othercopy1',array('label' => 'Other Email', 'validators' => array('emailAddress')))
                        ->addElement('text','othercopy2',array('label' => 'Other Email', 'validators' => array('emailAddress')))
                        ->addDisplayGroup(array('copyhospit','copychirurgeon','othercopy1','othercopy2'),'copies',
                                          array('legend' => 'Forward copies to:'))
                        ->addElement('textarea','statistics',array('label' => 'Statistics: Total and Active Members, Total Funds',
                                                                   'cols' => 50, 'rows' => 2, 'wrap' => 'virtual'))
                        ->addElement('textarea','activities',array('label' => 'Summary of Regular Activities',
                                                                   'cols' => 50, 'rows' => 10, 'wrap' => 'virtual'))
                        ->addElement('textarea','achievements',array('label' => 'Special Achievements and Ideas that Worked',
                                                                     'cols' => 50, 'rows' => 10, 'wrap' => 'virtual'))
                        ->addElement('textarea','events',array('label' => 'Summary of Events',
                                                               'cols' => 50, 'rows' => 10, 'wrap' => 'virtual'))
                        ->addElement('textarea','problems',array('label' => 'Problems of Note',
                                                                 'cols' => 50, 'rows' => 10, 'wrap' => 'virtual'))
                        ->addElement('textarea','questions',array('label' => 'Questions',
                                                                  'cols' => 50, 'rows' => 10, 'wrap' => 'virtual'))
                        ->addElement('textarea','plans',array('label' => 'Plans for the Future, Ideas, etc',
                                                              'cols' => 50, 'rows' => 10, 'wrap' => 'virtual'))
                        ->addElement('textarea','comments',array('label' => 'General Comments',
                                                                 'cols' => 50, 'rows' => 10, 'wrap' => 'virtual'))
                        ->addDisplayGroup(array('statistics','activities','achievements','events','problems','questions','plans','comments'),
                                          'report',array('legend' => 'Report Details'))
                        ->addElement('textarea','summarshal',array('label' => 'Marshal',
                                                                   'cols' => 50, 'rows' => 10, 'wrap' => 'virtual'))
                        ->addElement('textarea','sumherald',array('label' => 'Herald',
                                                                  'cols' => 50, 'rows' => 10, 'wrap' => 'virtual'))
                        ->addElement('textarea','sumartssci',array('label' => 'Arts and Sciences',
                                                                   'cols' => 50, 'rows' => 10, 'wrap' => 'virtual'))
                        ->addElement('textarea','sumreeve',array('label' => 'Reeve',
                                                                 'cols' => 50, 'rows' => 10, 'wrap' => 'virtual'))
                        ->addElement('textarea','sumconstable',array('label' => 'Constable',
                                                                     'cols' => 50, 'rows' => 10, 'wrap' => 'virtual'))
                        ->addElement('textarea','sumchirurgeon',array('label' => 'Chirurgeon',
                                                                      'cols' => 50, 'rows' => 10, 'wrap' => 'virtual'))
                        ->addElement('textarea','sumchronicler',array('label' => 'Chronicler and/or Webminister',
                                                                      'cols' => 50, 'rows' => 10, 'wrap' => 'virtual'))
                        ->addElement('textarea','sumchatelaine',array('label' => 'Chatelaine/Hospitaller',
                                                                      'cols' => 50, 'rows' => 10, 'wrap' => 'virtual'))
                        ->addElement('textarea','sumlists',array('label' => 'Lists',
                                                                 'cols' => 50, 'rows' => 10, 'wrap' => 'virtual'))
                        ->addDisplayGroup(array('summarshal','sumherald','sumartssci','sumreeve','sumconstable','sumchirurgeon','sumchronicler',
                                                'sumchatelaine','sumlists'),'officers',array('legend' => 'Summary of Officer Reports'));

            // Add fields for subgroups, if any.
            $db->setFetchMode(Zend_Db::FETCH_OBJ);
            $subgroups = $db->fetchAll("SELECT id, type, groupname FROM scagroup WHERE parentid={$db->quote($values['id'],Zend_Db::INT_TYPE)} " .
                                       "AND (status='live' OR status='proposed')");
            foreach($subgroups as $subgroup) {
                $detailsForm->addElement('textarea','subgroup'.$subgroup->id,array('label' => $subgroup->type . ' of ' . $subgroup->groupname,
                                                                                   'cols' => 50, 'rows' => 12, 'wrap' => 'virtual'));
                $subgroupFields[] = 'subgroup'.$subgroup->id;
            }
            if(!empty($subgroupFields)) $detailsForm->addDisplayGroup($subgroupFields,'subgroups',array('legend' => 'Subgroups'));

            $detailsForm->addElement('submit','submit',array('label' => 'Submit'))
                        ->addElement('submit','reset',array('label' => 'Reset'));

            if($detailsForm->isValid($_POST)) {
                $values = array_merge($values, $detailsForm->getValues());

                if($detailsForm->reset->isChecked()) $detailsForm->reset();
                elseif($detailsForm->submit->isChecked()) {
                    // Fetch fixed information.
                    $db->setFetchMode(Zend_Db::FETCH_ASSOC);
                    $values = array_merge($values, $db->fetchRow("SELECT type, groupname, parentid FROM scagroup WHERE id=" .
                                                                 $db->quote($values['id'],Zend_Db::INT_TYPE)));

                    // Update group record.
                    $values['lastreport'] = date('Y-m-d');
                    $keys = array('lastreport','scaname','realname','address','suburb','state','postcode','country','phone','email',
                                  'website','memnum');

                    try { $changed = $db->update('scagroup',array_intersect_key($values,array_flip($keys)),
                                                 "id={$db->quote($values['id'],Zend_Db::INT_TYPE)}");
                    } catch(Exception $e) { $this->view->message .= "<div class='bad'>Possible error updating group details.</div><br />\n"; }
                    $this->view->message .= $changed . " record(s) affected.<br />\n";

                    // Form report email.
                    $mailsubj  = 'Report from the ' . $values['type'] . ' of ' . $values['groupname'];
                    $mailbody  = $mailsubj;
                    $mailbody .= "\nDate: " . $values['lastreport'];
                    $mailbody .= "\nSubmitted by: " . $values['scaname'];
                    $mailbody .= " (" . $values['realname'] . ")";
                    $mailbody .= "\n\nSTATISTICS\n==========\n";
                    $mailbody .= $values['statistics'];
                    $mailbody .= "\n\nREGULAR ACTIVITIES\n=================\n";
                    $mailbody .= $values['activities'];
                    $mailbody .= "\n\nACHIEVEMENTS\n============\n";
                    $mailbody .= $values['achievements'];
                    $mailbody .= "\n\nEVENTS\n======\n";
                    $mailbody .= $values['events'];
                    $mailbody .= "\n\nPROBLEMS\n=========\n";
                    $mailbody .= $values['problems'];
                    $mailbody .= "\n\nQUESTIONS\n=========\n";
                    $mailbody .= $values['questions'];
                    $mailbody .= "\n\nPLANS\n=====\n";
                    $mailbody .= $values['plans'];
                    $mailbody .= "\n\nGENERAL COMMENTS\n=================\n";
                    $mailbody .= $values['comments'];

                    $mailbody .= "\n\nSUMMARY OF OFFICERS\n====================";

                    $mailbody .= "\n\n== Marshal\n" . $values['summarshal'];
                    $mailbody .= "\n\n== Herald\n" . $values['sumherald'];
                    $mailbody .= "\n\n== Arts and Sciences\n" . $values['sumartssci'];
                    $mailbody .= "\n\n== Reeve\n" . $values['sumreeve'];
                    $mailbody .= "\n\n== Constable\n" . $values['sumconstable'];
                    $mailbody .= "\n\n== Chirurgeon\n" . $values['sumchirurgeon'];
                    $mailbody .= "\n\n== Chronicler/Webminister\n" . $values['sumchronicler'];
                    $mailbody .= "\n\n== Chatelaine/Hospitaller\n" . $values['sumchatelaine'];
                    $mailbody .= "\n\n== Lists\n" . $values['sumlists'];

                    $mailbody .= "\n\nSUMMARY OF SUB-GROUPS\n======================";
                    foreach($subgroups as $subgroup) {
                        $mailbody .= "\nSummary report for " . $subgroup->type . " of " . $subgroup->groupname;
                        $mailbody .= "\n" . $values['subgroup'.$subgroup->id] . "\n";
                    }

                    $mailto[] = $db->fetchOne("SELECT email FROM scagroup WHERE id={$db->quote($values['parentid'],Zend_Db::INT_TYPE)}");
                    $mailto[] = $values['email'];
                    if($values['copyhospit']) $mailto[] = "hospitaller@lochac.sca.org";
                    if($values['copychirurgeon']) $mailto[] = "chirurgeon@lochac.sca.org";
                    if(!empty($values['othercopy1'])) $mailto[] = $values['othercopy1'];
                    if(!empty($values['othercopy2'])) $mailto[] = $values['othercopy2'];

                    $mailheaders = "From:" . $values['email'];

                    foreach($mailto as $address) {
                        if(mail($address,$mailsubj,$mailbody,$mailheaders)) {
                            $this->view->message .= "<div class='good'>Report sent to " . $address . "</div><br />\n";
                        } else {
                            $this->view->message .= "<div class='bad'>Report failed to send to " . $address . "</div><br />\n";
                        }
                    }

                }
            }

            if(!empty($values['id'])) {
                $db->setFetchMode(Zend_Db::FETCH_ASSOC);
                $defaults = $db->fetchRow("SELECT * FROM scagroup WHERE id={$db->quote($values['id'],Zend_Db::INT_TYPE)}");
                $detailsForm->setDefaults($defaults);
            }
        }

        if($auth['level'] == 'user') $groupSelectForm->setDefaults(array('groupid' => $auth['id']));
        $this->view->message .= "\n\n" . $groupSelectForm;
        if(!empty($detailsForm)) $this->view->message .= "\n\n" . $detailsForm;
    }

}

