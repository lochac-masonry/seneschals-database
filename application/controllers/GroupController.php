<?php

class GroupController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->_forward('roster');
    }

    public function listAction()
    {
        global $db;
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer('echoMessage', null, true);

        $sql = "SELECT a.groupname AS parent, b.groupname AS child, " .
               "b.memnum AS memnum, b.type AS type FROM scagroup a, " .
               "scagroup b WHERE a.id=b.parentid AND b.status<>'closed' " .
               "AND b.country<>'NZ' ORDER BY a.groupname, b.groupname";
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        try {
            $results = $db->fetchAll($sql);
        } catch(Exception $e) {
            die('Database error: ' . $e->getMessage);
        }

        foreach($results as $row) {
            $message .= $row->parent . "," . $row->child . "," .
                        $row->memnum . ",";
            if($row->type == 'College') {
                $message .= "College";
            }
            $message .= "<BR />\n";
        }

        $this->view->message = $message;
    }

    public function rosterAction()
    {
        $this->view->title = 'Group Roster';

        global $db;
        $db->setFetchMode(Zend_Db::FETCH_OBJ);
        $sql = 'SELECT groupname, type, status, scaname, realname, email, website, area FROM scagroup ORDER BY groupname';
        $results = $db->fetchAll($sql);

        if(count($results) == 0) {
            $this->_helper->viewRenderer->setNoRender();
        } else {
            $this->view->data = $results;
        }
    }

    public function editAction()
    {
        $auth = authenticate();
        global $db;
        if($auth['level'] != 'admin') {
            throw new Exception('User not authorised for this task.');
            return;
        }

        $this->view->title = 'Edit Group Details';
        $this->view->message = '';
        $groupList = $db->fetchPairs('SELECT id, groupname FROM scagroup ORDER BY groupname');
        $values['id'] = 'new'; // Default value for group select box.

                                                            //----------------------------------------------------------
                                                            // Build group selection form
                                                            //----------------------------------------------------------
        $groupSelectForm = new SenDb_Form_GroupSelect(array('method' => 'get'));
        $groupSelectForm->groupid->options = array_merge(
            array('new' => 'New Group'),
            $groupList
        );

        if($groupSelectForm->isValid($_GET)) {
            //Show relevant details for the selected group.
            $values['id'] = $groupSelectForm->getValue('groupid');

                                                            //----------------------------------------------------------
                                                            // Build group edit form
                                                            //----------------------------------------------------------
            $detailsForm = new SenDb_Form_Group_Edit(array('method' => 'post'));
            $detailsForm->parentid->options = $groupList;

                                                            //----------------------------------------------------------
                                                            // Process the form - update or create group
                                                            //----------------------------------------------------------
            if($detailsForm->isValid($_POST)) {
                $values = array_merge($values, $detailsForm->getValues());
                unset($values['submit'], $values['reset']);

                if($detailsForm->reset->isChecked()) {
                    $detailsForm->reset();

                } elseif($values['id'] == 'new') {
                    // Create a new group.
                    $values['id'] = NULL;
                    try {
                        $changed = $db->insert('scagroup',$values);

                        if($changed == 1) {
                            $this->view->message .= "<div class='good'>Successfully added group '{$values['groupname']}'. <a href='" .
                                                                  Zend_Layout::getMvcInstance()->relativeUrl .
                                                                  "/group/edit?groupid={$db->lastInsertId()}'>Click to continue.</a></div><br />\n";

                        } else {
                            $this->view->message .= "<div class='bad'>Creating '{$values['groupname']}' failed. This is usually caused by a " .
                                                     "database issue. <a href='" . Zend_Layout::getMvcInstance()->relativeUrl .
                                                     "/group/edit?groupid={$db->lastInsertId()}'>Refresh to check.</a></div><br />\n";
                        }

                    } catch(Exception $e) {
                        $this->view->message .= "<div class='bad'>Creating '{$values['groupname']}' failed due to a database " .
                                                                    "issue. Please try again.</div><br />\n";
                    }

                    $values['id'] = 'new';

                } else {
                    // Update existing group.
                    try {
                        $changed = $db->update(
                            'scagroup',
                            $values,
                            "id={$db->quote($values['id'],Zend_Db::INT_TYPE)}"
                        );

                        if($changed == 1) {
                            $this->view->message .= "<div class='good'>Successfully updated {$values['groupname']}. <a href='" .
                                                                  Zend_Layout::getMvcInstance()->relativeUrl .
                                                                  "/group/edit?groupid={$values['id']}'>Click to continue.</a></div><br />\n";

                        } else {
                            $this->view->message .= "<div class='bad'>Updating {$values['groupname']} failed. The group may not exist. <a href='" .
                                                     Zend_Layout::getMvcInstance()->relativeUrl .
                                                     "/group/edit?groupid={$values['id']}'>Refresh to check.</a></div><br />\n";
                        }

                    } catch(Exception $e) {
                        $this->view->message .= "<div class='bad'>Updating {$values['groupname']} failed due to a " .
                                                                    "database issue. Please try again.</div><br />\n";
                    }

                }
            }

            $defaults = array(
                'groupname' => '',
                'type'      => 'Barony',
                'status'    => 'live',
                'parentid'  => 1
            );

            if(!empty($values['id'])
              && $values['id'] <> 'new') {
                $db->setFetchMode(Zend_Db::FETCH_ASSOC);
                $defaults = $db->fetchRow("SELECT * FROM scagroup WHERE id={$db->quote($values['id'],Zend_Db::INT_TYPE)}");
            }
            $detailsForm->setDefaults($defaults);
        }

        $groupSelectForm->setDefaults(array('groupid' => $values['id']));

        $this->view->forms = $groupSelectForm;
        if(!empty($detailsForm)) {
            $this->view->forms .= "\n\n" . $detailsForm;
        }
    }

    public function closeAction()
    {
        $auth = authenticate();
        global $db;
        if($auth['level'] != 'admin') {
            throw new Exception('User not authorised for this task.');
            return;
        }

        $this->view->title = 'Close Group';
        $this->view->message = '';
        $groupList = $db->fetchPairs('SELECT id, groupname FROM scagroup ORDER BY groupname');

                                                            //----------------------------------------------------------
                                                            // Build the close group form
                                                            //----------------------------------------------------------
        $form = new SenDb_Form_Group_Close(array('method' => 'post'));
        $form->group_close->options = $groupList;
        $form->group_get->options = $groupList;

                                                            //----------------------------------------------------------
                                                            // Process the form - close the group
                                                            //----------------------------------------------------------
        if($form->isValid($_POST)) {
            // Do the assignment.
            $values = $form->getValues();

            if($form->confirm->isChecked()) {
                try {
                    $updateCount = $db->update(
                        'postcode',
                        array('groupid' => $values['group_get']),
                        "groupid={$db->quote($values['group_close'],Zend_Db::INT_TYPE)}"
                    );

                    $this->view->message .= "<div class='good'>{$groupList[$values['group_close']]} closed successfully. {$updateCount} " .
                                            "postcodes transferred to {$groupList[$values['group_get']]}.</div><br />\n";

                } catch(Exception $e) {
                    $this->view->message .= "<div class='bad'>Update failed due to database error. Please " .
                                                                "try again.</div><br />\n";
                }

            } else {
                $this->view->message .= "<div class='bad'>Confirm was not checked. No action taken.</div><br />\n";
            }

        }

        $this->view->form = $form;
    }

    public function aliasesAction()
    {
        $auth = authenticate();
        global $db;
        if($auth['level'] != 'admin' && $auth['level'] != 'user') {
            throw new Exception('User not authorised for this task.');
            return;
        }

        $this->view->title = 'Manage Group Email Aliases';
        $this->view->message = '';
        $groupList = $db->fetchPairs('SELECT id, groupname FROM scagroup ORDER BY groupname');

                                                            //----------------------------------------------------------
                                                            // Build group selection form - only enabled for admin
                                                            //----------------------------------------------------------
        $groupSelectForm = new SenDb_Form_GroupSelect(array('method' => 'get'));
        $groupSelectForm->groupid->options = $groupList;

        if($auth['level'] != 'admin') {
            $groupSelectForm->groupid->disabled = true;
            $groupSelectForm->submit->disabled = true;
        }

                                                            //----------------------------------------------------------
                                                            // Once group is selected, display the alias forms
                                                            //----------------------------------------------------------
        if($groupSelectForm->isValid($_GET)) {
            if($auth['level'] == 'admin') {
                $groupid = $groupSelectForm->getValue('groupid');
            } else {
                $groupid = $auth['id'];
            }

            // Find which domains this group can edit, form an appropriate regex and explanatory message.
            $domains = $db->fetchCol("SELECT domain FROM domains WHERE groupid={$db->quote($groupid,Zend_Db::INT_TYPE)}");
            $emailRegex = '/^[^@]+@';
            $this->view->message .= 'You are currently permitted to manage aliases with domain';

            if(count($domains) > 0) {
                $emailRegex .= '((' . $domains[0];
                $this->view->message .= ' ' . $domains[0] . '.lochac.sca.org';

                for($i = 1; $i < count($domains); $i++) {
                    $emailRegex .= '|' . $domains[$i];
                    $this->view->message .= ', ' . $domains[$i] . '.lochac.sca.org';
                }

                $emailRegex .= ').)?';
                $this->view->message .= ' or';
            }

            $emailRegex .= 'lochac.sca.org$/';
            $this->view->message .= ' lochac.sca.org. Request others from Kingdom Seneschal.' . "<br /><br />\n\n";

            // Retrieve existing aliases
            $rows = $db->fetchAssoc("SELECT row_id, alias, address FROM virtusers WHERE groupid={$db->quote($groupid,Zend_Db::INT_TYPE)}");
            foreach($rows as $id => $row) {
                $values['alias'.$id] = $row['alias'];
                $values['address'.$id] = $row['address'];

                                                            //----------------------------------------------------------
                                                            // Build existing alias form
                                                            //----------------------------------------------------------
                $aliasForms[$id] = new SenDb_Form_Group_Alias(array(
                    'method' => 'post',
                    'suffix' => $id
                ));
                $aliasForms[$id]->getElement('alias'.$id)->addValidator(
                    'regex',
                    false,
                    array('pattern' => $emailRegex)
                );

                                                            //----------------------------------------------------------
                                                            // Process an existing alias - update or delete
                                                            //----------------------------------------------------------
                if($aliasForms[$id]->isValid($_POST)) {
                    $values = $aliasForms[$id]->getValues();

                    if($aliasForms[$id]->getElement('delete'.$id)->isChecked()) {
                        try {
                            $changed = $db->delete('virtusers', "row_id={$db->quote($id,Zend_Db::INT_TYPE)}");

                            if($changed == 1) {
                                $this->view->message .= "<div class='good'>Successfully deleted alias '{$values['alias'.$id]}'. " .
                                                                      "<a href='" . Zend_Layout::getMvcInstance()->relativeUrl .
                                                                      "/group/aliases?groupid={$groupid}'>Click to continue.</a></div><br />\n";

                            } else {
                                $this->view->message .= "<div class='bad'>Deleting '{$values['alias'.$id]}' failed. The alias may have " .
                                                         "already been deleted. <a href='" . Zend_Layout::getMvcInstance()->relativeUrl .
                                                         "/group/aliases?groupid={$groupid}'>Refresh to check.</a></div><br />\n";
                            }

                        } catch(Exception $e) {
                            $this->view->message .= "<div class='bad'>Deleting '{$values['alias'.$id]}' failed due to a " .
                                                                        "database error. Please try again.</div><br />\n";
                        }

                    } elseif($aliasForms[$id]->getElement('submit'.$id)->isChecked()) {
                        try {
                            $changed = $db->update(
                                'virtusers',
                                array( // set
                                    'alias' => $values['alias'.$id],
                                    'address' => $values['address'.$id]
                                ),
                                "row_id={$db->quote($id,Zend_Db::INT_TYPE)}" // where
                            );

                            if($changed == 1) {
                                $this->view->message .= "<div class='good'>Successfully updated alias '{$values['alias'.$id]}'. " .
                                                                      "<a href='" . Zend_Layout::getMvcInstance()->relativeUrl .
                                                                      "/group/aliases?groupid={$groupid}'>Click to continue.</a></div><br />\n";

                            } else {
                                $this->view->message .= "<div class='bad'>Updating '{$values['alias'.$id]}' failed. The alias might not " .
                                                         "exist. <a href='" . Zend_Layout::getMvcInstance()->relativeUrl .
                                                         "/group/aliases?groupid={$groupid}'>Refresh to check.</a></div><br />\n";
                            }

                        } catch(Exception $e) {
                            $this->view->message .= "<div class='bad'>Updating '{$values['alias'.$id]}' failed due to a " .
                                                                        "database error. Please try again.</div><br />\n";
                        }

                    }

                } elseif($aliasForms[$id]->getElement('submit'.$id)->isChecked()
                  || $aliasForms[$id]->getElement('delete'.$id)->isChecked()) {
                    // Form didn't validate, even though a button was pressed -> invalid address.
                    $this->view->message .= "<div class='bad'>Alias and/or destination address invalid. Destination must be a valid email " .
                                            "address, and the alias must be @ one of your listed domains.</div><br />\n";
                }

                $aliasForms[$id]->setDefaults($values);
            }

                                                            //----------------------------------------------------------
                                                            // Build new alias form
                                                            //----------------------------------------------------------
            $aliasForms['new'] = new SenDb_Form_Group_AliasNew(array('method' => 'post'));
            $aliasForms['new']->aliasnew->addValidator(
                'regex',
                false,
                array('pattern' => $emailRegex)
            );

                                                            //----------------------------------------------------------
                                                            // Process new alias - insert
                                                            //----------------------------------------------------------
            if($aliasForms['new']->isValid($_POST)) {
                $values = $aliasForms['new']->getValues();

                // Is the provided alias already in use?
                if(0 < $db->fetchOne("SELECT COUNT(alias) AS count FROM virtusers WHERE alias={$db->quote($values['aliasnew'])}")) {
                    $usedby = $db->fetchOne("SELECT groupid FROM virtusers WHERE alias={$db->quote($values['aliasnew'])}");

                    $this->view->message .= "<div class='bad'>'{$values['aliasnew']}' has been previously used by another group. " .
                                            "<a href='" . Zend_Layout::getMvcInstance()->relativeUrl .
                                            "/group/aliases?groupid={$usedby}'>Details</a></div><br />\n";
                } else {
                    try {
                        $changed = $db->insert(
                            'virtusers',
                            array(
                                'alias'   => $values['aliasnew'],
                                'address' => $values['addressnew'],
                                'groupid' => $groupid,
                                'comment' => ''
                            )
                        );

                        if($changed == 1) {
                            $this->view->message .= "<div class='good'>Successfully added alias '{$values['aliasnew']}'. " .
                                                                  "<a href='" . Zend_Layout::getMvcInstance()->relativeUrl .
                                                                  "/group/aliases?groupid={$groupid}'>Click to continue.</a></div><br />\n";

                        } else {
                            $this->view->message .= "<div class='bad'>Adding alias '{$values['aliasnew']}' may have failed. <a href='" .
                                                     Zend_Layout::getMvcInstance()->relativeUrl .
                                                     "/group/aliases?groupid={$groupid}'>Refresh to check</a>, then try again.</div><br />\n";
                        }

                    } catch(Exception $e) {
                        $this->view->message .= "<div class='bad'>Adding alias '{$values['aliasnew']}' failed due to a " .
                                                                    "database error. Please try again.</div><br />\n";
                    }

                }
            } elseif($aliasForms['new']->submitnew->isChecked()) {
                $this->view->message .= "<div class='bad'>Alias and/or destination address invalid. Destination must be a valid email address, " .
                                        "and the alias must be @ one of your listed domains.</div><br />\n";
            }
        }

        if(isset($aliasForms)) {
            $this->view->aliasForms = $aliasForms;
        } else {
            $this->view->aliasForms = array();
        }

        if($auth['level'] == 'user') {
            $groupSelectForm->setDefaults(array('groupid' => $auth['id']));
        }
        $this->view->groupSelectForm = $groupSelectForm;
    }

    public function domainsAction()
    {
        $auth = authenticate();
        global $db;
        if($auth['level'] != 'admin') {
            throw new Exception('User not authorised for this task.');
            return;
        }

        $this->view->title = 'Manage Group Domains';
        $this->view->message = '';
        $groupList = $db->fetchPairs('SELECT id, groupname FROM scagroup ORDER BY groupname');

        // Show listing of configured domains.
        $rows = $db->fetchAssoc("SELECT id, groupid, domain FROM domains");
        foreach($rows as $id => $row) {
            $values['groupid'.$id] = $row['groupid'];
            $values['domain'.$id] = $row['domain'];

                                                            //----------------------------------------------------------
                                                            // Build existing domain form
                                                            //----------------------------------------------------------
            $domainForms[$id] = new SenDb_Form_Group_Domain(array(
                'method' => 'post',
                'suffix' => $id
            ));
            $domainForms[$id]->getElement('groupid'.$id)->options = $groupList;

                                                            //----------------------------------------------------------
                                                            // Process existing domain - update or delete
                                                            //----------------------------------------------------------
            if($domainForms[$id]->isValid($_POST)) {
                $values = $domainForms[$id]->getValues();

                // Find the previous settings of this domain
                $db->setFetchMode(Zend_Db::FETCH_ASSOC);
                $old = $db->fetchRow("SELECT groupid, domain FROM domains WHERE id={$db->quote($id,Zend_Db::INT_TYPE)}");

                // Find all aliases of the group, and check if any use this domain.
                $aliases = $db->fetchCol("SELECT alias FROM virtusers WHERE groupid={$db->quote($old['groupid'],Zend_Db::INT_TYPE)}");
                $aliasCount = 0;
                foreach($aliases as $alias) {
                    if(1 === preg_match('/@'.$old['domain'].'/',$alias)) {
                        $aliasCount++;
                    }
                }

                // If no aliases use the group/domain combo this was, safe to update.
                if($aliasCount == 0) {
                    if($domainForms[$id]->getElement('delete'.$id)->isChecked()) {
                        try {
                            $changed = $db->delete('domains', "id={$db->quote($id,Zend_Db::INT_TYPE)}");

                            if($changed == 1) {
                                $this->view->message .= "<div class='good'>Successfully deleted domain '{$values['domain'.$id]}'. " .
                                                                      "<a href='" . Zend_Layout::getMvcInstance()->relativeUrl .
                                                                      "/group/domains'>Click to continue.</a></div><br />\n";

                            } else {
                                $this->view->message .= "<div class='bad'>Deleting '{$values['domain'.$id]}' failed. The domain may have already " .
                                                         "been deleted. <a href='" . Zend_Layout::getMvcInstance()->relativeUrl .
                                                         "/group/domains'>Refresh to check.</a></div><br />\n";
                            }

                        } catch(Exception $e) {
                            $this->view->message .= "<div class='bad'>Deleting '{$values['alias'.$id]}' failed due to a " .
                                                                        "database error. Please try again.</div><br />\n";
                        }

                    } elseif($domainForms[$id]->getElement('submit'.$id)->isChecked()) {
                        try {
                            $changed = $db->update(
                                'domains',
                                array( // set
                                    'groupid' => $values['groupid'.$id],
                                    'domain'  => $values['domain'.$id]
                                ),
                                "id={$db->quote($id,Zend_Db::INT_TYPE)}" // where
                            );

                            if($changed == 1) {
                                $this->view->message .= "<div class='good'>Successfully updated domain '{$values['domain'.$id]}'. " .
                                                                      "<a href='" . Zend_Layout::getMvcInstance()->relativeUrl .
                                                                      "/group/domains'>Click to continue.</a></div><br />\n";

                            } else {
                                $this->view->message .= "<div class='bad'>Updating '{$values['domain'.$id]}' failed. The domain might not exist. " .
                                                         "<a href='" . Zend_Layout::getMvcInstance()->relativeUrl .
                                                         "/group/domains'>Refresh to check.</a></div><br />\n";
                            }

                        } catch(Exception $e) {
                            $this->view->message .= "<div class='bad'>Updating '{$values['domain'.$id]}' failed due to a " .
                                                                        "database error. Please try again.</div><br />\n";
                        }

                    }

                } else {
                    $this->view->message .= "<div class='bad'>That group has {$aliasCount} aliases under the domain {$old['domain']}.lochac.sca.org. " .
                                            "Please remove these aliases or move them to a different domain before changing this domain.</div><br />\n";
                }

            } elseif($domainForms[$id]->getElement('submit'.$id)->isChecked()) {
                // form invalid, but groupid came from our list and submit was pressed - domain must be invalid
                $this->view->message .= "<div class='bad'>Domain invalid. Must be lower case letters only.</div><br />\n";
            }

            $domainForms[$id]->setDefaults($values);
        }

                                                            //----------------------------------------------------------
                                                            // Build new domain form
                                                            //----------------------------------------------------------
        $domainForms['new'] = new SenDb_Form_Group_DomainNew(array('method' => 'post'));
        $domainForms['new']->groupidnew->options = $groupList;

                                                            //----------------------------------------------------------
                                                            // Process new domain - insert
                                                            //----------------------------------------------------------
        if($domainForms['new']->isValid($_POST)) {
            $values = $domainForms['new']->getValues();

            // Does that group already have that domain?
            if(0 < $db->fetchOne("SELECT COUNT(groupid) AS count FROM domains WHERE domain={$db->quote($values['domainnew'])}" .
                                                                               "AND groupid={$db->quote($values['groupidnew'],Zend_Db::INT_TYPE)}")) {
                $this->view->message .= "<div class='bad'>That group already has access to that domain.</div><br />\n";
            } else {
                try {
                    $changed = $db->insert(
                        'domains',
                        array(
                            'domain'  => $values['domainnew'],
                            'groupid' => $values['groupidnew']
                        )
                    );

                    if($changed == 1) {
                        $this->view->message .= "<div class='good'>Successfully added domain '{$values['domainnew']}'. " .
                                                              "<a href='" . Zend_Layout::getMvcInstance()->relativeUrl .
                                                              "/group/domains'>Click to continue.</a></div><br />\n";

                    } else {
                        $this->view->message .= "<div class='bad'>Adding domain '{$values['domainnew']}' may have failed. <a href='" .
                                                 Zend_Layout::getMvcInstance()->relativeUrl .
                                                 "/group/domains'>Refresh to check</a>, then try again.</div><br />\n";
                    }

                } catch(Exception $e) {
                    $this->view->message .= "<div class='bad'>Adding domain '{$values['domainnew']}' failed due to a " .
                                                                "database error. Please try again.</div><br />\n";
                }

            }
        } elseif($domainForms['new']->submitnew->isChecked()) {
            $this->view->message .= "<div class='bad'>Domain invalid. Must be lower case letters only.</div><br />\n";
        }

        if(isset($domainForms)) {
            $this->view->domainForms = $domainForms;
        } else {
            $this->view->domainForms = array();
        }
    }

    public function baronBaronessAction()
    {
        $auth = authenticate();
        global $db;
        if($auth['level'] != 'admin' && $auth['level'] != 'user') {
            throw new Exception('User not authorised for this task.');
            return;
        }

        $this->view->title = 'Baron and Baroness Details';
        $this->view->message = '';
        $groupList = $db->fetchPairs("SELECT id, groupname FROM scagroup WHERE type='Barony' ORDER BY groupname");

                                                            //----------------------------------------------------------
                                                            // Build group select form - enabled only for admin,
                                                            // disabled for baronies, not rendered for other groups
                                                            //----------------------------------------------------------
        $groupSelectForm = new SenDb_Form_GroupSelect(array('method' => 'get'));
        $groupSelectForm->groupid->options = $groupList;

        if($auth['level'] != 'admin') {
            $groupSelectForm->groupid->disabled = true;
            $groupSelectForm->submit->disabled = true;
        }

        if($auth['level'] == 'user'
          && !array_key_exists($auth['id'],$groupList)) {
            $this->view->message .= "Available for baronies only.<br />\n";
        } elseif($groupSelectForm->isValid($_GET)) {
            //Show relevant details for the selected group.
            if($auth['level'] == 'admin') {
                $values['groupid'] = $groupSelectForm->getValue('groupid');
            } else {
                $values['groupid'] = $auth['id'];
            }

                                                            //----------------------------------------------------------
                                                            // Build B&B editing form
                                                            //----------------------------------------------------------
            $detailsForm = new SenDb_Form_Group_Nobility(array('method' => 'post'));

                                                            //----------------------------------------------------------
                                                            // Process Baronial details - insert or update
                                                            //----------------------------------------------------------
            if($detailsForm->isValid($_POST)) {
                $values = array_merge($values, $detailsForm->getValues());
                unset($values['submit'], $values['reset']);

                $existing = $db->fetchOne("SELECT COUNT(groupid) AS count FROM barony WHERE groupid={$db->quote($values['groupid'],Zend_Db::INT_TYPE)}");

                if($detailsForm->reset->isChecked()) {
                    $detailsForm->reset();

                } elseif($detailsForm->submit->isChecked()) {
                    if($existing == 0) {
                        // no entry exists, insert new
                        try {
                            $changed = $db->insert('barony',$values);

                            if($changed == 1) {
                                $this->view->message .= "<div class='good'>Successfully added barony. <a href='" .
                                                                      Zend_Layout::getMvcInstance()->relativeUrl . "/group/baron-baroness?groupid=" .
                                                                      "{$values['groupid']}'>Click to continue.</a></div><br />\n";

                            } else {
                                $this->view->message .= "<div class='bad'>Adding barony failed. The barony may already exist. " .
                                                         "<a href='" . Zend_Layout::getMvcInstance()->relativeUrl .
                                                         "/group/baron-baroness?groupid={$values['groupid']}'>Refresh to check.</a></div><br />\n";
                            }

                        } catch(Exception $e) {
                            $this->view->message .= "<div class='bad'>Adding barony failed due to a database error. " .
                                                                        "Please try again.</div><br />\n";
                        }

                    } else {
                        // entry exists, update
                        try {
                            $changed = $db->update(
                                'barony',
                                $values,
                                "groupid={$db->quote($values['groupid'],Zend_Db::INT_TYPE)}"
                            );

                            if($changed == 1) {
                                $this->view->message .= "<div class='good'>Successfully updated barony. <a href='" .
                                                                      Zend_Layout::getMvcInstance()->relativeUrl . "/group/baron-baroness?groupid=" .
                                                                      "{$values['groupid']}'>Click to continue.</a></div><br />\n";

                            } else {
                                $this->view->message .= "<div class='bad'>Updating barony failed. The barony might not exist. <a href='" .
                                                         Zend_Layout::getMvcInstance()->relativeUrl .
                                                         "/group/baron-baroness?groupid={$values['groupid']}'>Refresh to check.</a></div><br />\n";
                            }

                        } catch(Exception $e) {
                            $this->view->message .= "<div class='bad'>Updating barony failed due to a database error. " .
                                                                        "Please try again.</div><br />\n";
                        }

                    }

                }
            }

                                                            //----------------------------------------------------------
                                                            // Pre-populate with existing Baronial details, if any
                                                            //----------------------------------------------------------
            if(0 < $db->fetchOne("SELECT COUNT(groupid) AS count FROM barony WHERE groupid={$db->quote($values['groupid'],Zend_Db::INT_TYPE)}")) {
                $db->setFetchMode(Zend_Db::FETCH_ASSOC);
                $defaults = $db->fetchRow("SELECT * FROM barony WHERE groupid={$db->quote($values['groupid'],Zend_Db::INT_TYPE)}");
                $detailsForm->setDefaults($defaults);
            }
        }

                                                            //----------------------------------------------------------
                                                            // Select user's group, and render forms
                                                            //----------------------------------------------------------
        if($auth['level'] == 'user'
          && array_key_exists($auth['id'],$groupList)) {
            $groupSelectForm->setDefaults(array('groupid' => $auth['id']));
        }
        $this->view->forms = $groupSelectForm;
        if(!empty($detailsForm)) {
            $this->view->forms .= "\n\n" . $detailsForm;
        }
    }

}

