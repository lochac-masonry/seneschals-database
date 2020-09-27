<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Form;
use Laminas\Db\Sql\{Delete, Insert, Select, Sql, Update};
use Laminas\View\Model\ViewModel;

class GroupController extends DatabaseController
{
    public function listAction()
    {
        $sql = "SELECT a.groupname AS parent, b.groupname AS child, " .
               "b.memnum AS memnum, IF(b.type = 'College', 'College', '') AS isCollege " .
               "FROM scagroup a, scagroup b " .
               "WHERE a.id = b.parentid AND b.status <> 'closed' " .
               "AND b.country <> 'NZ' ORDER BY a.groupname, b.groupname";

        return (new ViewModel([
            'groupResultSet' => $this->db->query($sql, [])->toArray(),
        ]))->setTerminal(true);
    }

    public function rosterAction()
    {
        $this->layout()->title = 'Group Roster';
        $this->layout()->fullWidth = true;
        $db = $this->db;

        $sql = (new Sql($db))->buildSqlString(
            (new Select())
                ->columns([
                    'groupname',
                    'type',
                    'status',
                    'scaname',
                    'realname',
                    'email',
                    'website',
                    'area',
                ])
                ->from('scagroup')
                ->where(function ($where) {
                    $where
                        ->notEqualTo('status', 'closed')
                        ->notEqualTo('status', 'dormant');
                })
                ->order('groupname')
        );

        return [
            'groupResultSet' => $db->query($sql, [])->toArray(),
        ];
    }

    public function editAction()
    {
        $this->layout()->title = 'Edit Group Details';
        $db = $this->db;
        $authResponse = $this->auth()->ensureLevel(['admin']);
        if ($authResponse) {
            return $authResponse;
        }

        $groupList = $this->arrayIndex(
            $db->query('SELECT id, groupname FROM scagroup ORDER BY groupname', []),
            'id',
            'groupname'
        );

                                                            //----------------------------------------------------------
                                                            // Group selection form
                                                            //----------------------------------------------------------
        $groupSelectForm = new Form\GroupSelect(['new' => 'New Group'] + $groupList);
        $detailsForm = null;

        $request = $this->getRequest();
        $groupSelectForm->setData(['groupid' => $request->getQuery()['groupid'] ?: 'new']);

        if ($groupSelectForm->isValid()) {
            $groupId = $groupSelectForm->getData()['groupid'];
            $operation = $groupId == 'new' ? 'Creating' : 'Updating';
                                                            //----------------------------------------------------------
                                                            // Build the group edit form
                                                            //----------------------------------------------------------
            $detailsForm = new Form\Group\Edit($groupList);

            if ($groupId == 'new') {
                // Set some defaults.
                $detailsForm->setData([
                    'groupDetails' => [
                        'type'     => 'Barony',
                        'status'   => 'live',
                        'parentid' => 1,
                    ],
                ]);
            } else {
                // Load existing data from DB.
                $initialData = (array) $db->query(
                    (new Sql($db))->buildSqlString(
                        (new Select())
                            ->from('scagroup')
                            ->where(['id' => $groupId])
                    ),
                    []
                )->toArray()[0];
                $detailsForm->setData([
                    'groupDetails' => array_intersect_key($initialData, array_flip([
                        'groupname',
                        'area',
                        'website',
                        'type',
                        'status',
                        'parentid',
                    ])),
                    'senDetails' => array_intersect_key($initialData, array_flip([
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
                        'lastreport',
                    ])),
                ]);
            }

                                                            //----------------------------------------------------------
                                                            // Process the submitted details
                                                            //----------------------------------------------------------
            if ($request->isPost()) {
                $detailsForm->setData($request->getPost());

                if ($detailsForm->isValid()) {
                    $values = $detailsForm->getData();

                    $fieldsToUpdate = $values['senDetails'] + $values['groupDetails'];

                    if ($groupId == 'new') {
                        $result = $db->query(
                            (new Sql($db))->buildSqlString(
                                (new Insert('scagroup'))
                                    ->values($fieldsToUpdate)
                            ),
                            $db::QUERY_MODE_EXECUTE
                        );
                        $groupId = $result->getGeneratedValue();
                    } else {
                        $result = $db->query(
                            (new Sql($db))->buildSqlString(
                                (new Update('scagroup'))
                                    ->set($fieldsToUpdate)
                                    ->where(['id' => $groupId])
                            ),
                            $db::QUERY_MODE_EXECUTE
                        );
                    }

                    if ($result->getAffectedRows() == 0) {
                        // Something went wrong
                        $this->alert()->bad("{$operation} group failed, please try again.");
                    } else {
                        $refreshUrl = $this->currentUrl();
                        $this->alert()->good(
                            "{$operation} group succeeded. <a href='{$refreshUrl}'>Click to continue</a>."
                        );
                    }
                }
            }
        }

        return [
            'groupSelectForm' => $groupSelectForm,
            'detailsForm'     => $detailsForm,
        ];
    }

    public function closeAction()
    {
        $this->layout()->title = 'Close Group';
        $db = $this->db;
        $authResponse = $this->auth()->ensureLevel(['admin']);
        if ($authResponse) {
            return $authResponse;
        }

        $groupList = $this->arrayIndex(
            $db->query('SELECT id, groupname FROM scagroup ORDER BY groupname', []),
            'id',
            'groupname'
        );

        $form = new Form\Group\Close($groupList);
        $viewModel = [
            'closeForm' => $form,
        ];

        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $viewModel;
        }

        $form->setData($request->getPost());
        if (!$form->isValid()) {
            return $viewModel;
        }

        $values = $form->getData()['close'];
        if (!$form->get('close')->get('confirm')->isChecked()) {
            $this->alert('Confirm was not checked, no action taken.');
            return $viewModel;
        }

        $result = $db->query(
            (new Sql($db))->buildSqlString(
                (new Update('postcode'))
                    ->set(['groupid' => $values['group_get']])
                    ->where(['groupid' => $values['group_close']])
            ),
            $db::QUERY_MODE_EXECUTE
        );
        $this->alert()->good(
            "{$result->getAffectedRows()} transferred from " .
            "{$groupList[$values['group_close']]} to {$groupList[$values['group_get']]}."
        );
        return $viewModel;
    }

    public function aliasesAction()
    {
        $this->layout()->title = 'Manage Group Email Aliases';
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
                                                            // Group selection form
                                                            // Enabled for admin, disabled for regular groups
                                                            //----------------------------------------------------------
        $groupSelectForm = new Form\GroupSelect([0 => 'Unassigned'] + $groupList);
        $viewModel = [
            'groupSelectForm' => $groupSelectForm,
            'aliasForms'      => [],
        ];

        $request = $this->getRequest();
        if ($this->auth()->getLevel() == 'admin') {
            $groupSelectForm->setData(['groupid' => $request->getQuery()['groupid'] ?: 0]);
        } else {
            $groupSelectForm->setData(['groupid' => $this->auth()->getId()]);
            $groupSelectForm->get('groupid')->setAttribute('disabled', true);
            $groupSelectForm->get('groupsubmit')->setAttribute('disabled', true);
        }

        if (!$groupSelectForm->isValid()) {
            return $viewModel;
        }

        $groupId = $groupSelectForm->getData()['groupid'];
        $refreshUrl = $this->currentUrl();

        // Find which domains this group can edit, form an appropriate regex and explanatory message.
        $domains = $db->query(
            (new Sql($db))->buildSqlString(
                (new Select())
                    ->columns(['domain'])
                    ->from('domains')
                    ->where(['groupid' => $groupId])
            ),
            []
        )->toArray();
        $aliasRegex = '.+@';
        $permittedDomains = 'You are currently permitted to manage aliases with domain';

        if (count($domains) > 0) {
            $aliasRegex .= '((' . $domains[0]['domain'];
            $permittedDomains .= ' ' . $domains[0]['domain'] . '.lochac.sca.org';

            for ($i = 1; $i < count($domains); $i++) {
                $aliasRegex .= '|' . $domains[$i]['domain'];
                $permittedDomains .= ', ' . $domains[$i]['domain'] . '.lochac.sca.org';
            }

            $aliasRegex .= ').)?';
            $permittedDomains .= ' or';
        }

        $aliasRegex .= 'lochac.sca.org';
        $permittedDomains .= ' lochac.sca.org. Request others from Kingdom Seneschal.';
        $viewModel['permittedDomains'] = $permittedDomains;

                                                            //----------------------------------------------------------
                                                            // Build the alias forms
                                                            //----------------------------------------------------------
        $existingAliases = $db->query(
            (new Sql($db))->buildSqlString(
                (new Select())
                    ->columns(['row_id', 'alias', 'address'])
                    ->from('virtusers')
                    ->where(['groupid' => $groupId])
            ),
            []
        )->toArray();
        foreach ($existingAliases as $alias) {
            $aliasId = $alias['row_id'];
            $aliasForm = new Form\Group\Alias($aliasId, $aliasRegex, true);
            $aliasForm->setData([
                'alias' . $aliasId   => $alias['alias'],
                'address' . $aliasId => $alias['address'],
            ]);
            $viewModel['aliasForms'][$aliasId] = $aliasForm;
        }
        // Plus one to allow creating a new alias.
        $viewModel['aliasForms']['new'] = new Form\Group\Alias('new', $aliasRegex, false);

        if (!$request->isPost()) {
            return $viewModel;
        }

                                                            //----------------------------------------------------------
                                                            // Process the alias forms
                                                            //----------------------------------------------------------
        $requestData = $request->getPost();
        foreach ($viewModel['aliasForms'] as $aliasId => $aliasForm) {
            $create = isset($requestData['create' . $aliasId]);
            $update = isset($requestData['update' . $aliasId]);
            $delete = isset($requestData['delete' . $aliasId]);

            if (!$create && !$update && !$delete) {
                // Nothing to do for this alias.
                continue;
            }

            $aliasForm->setData($requestData);
            if (!$aliasForm->isValid()) {
                // Report validation issues for each element.
                foreach ($aliasForm->getMessages() as $element => $messages) {
                    $elementName = '';
                    if (strpos($element, 'alias') === 0) {
                        $elementName = 'Alias: ';
                    } elseif (strpos($element, 'address') === 0) {
                        $elementName = 'Destination Address: ';
                    }

                    // Get first message value - only expect one as validator chain stops on first error.
                    $this->alert()->bad($elementName . reset($messages));
                }
                continue;
            }

            if ($delete) {
                $db->query(
                    (new Sql($db))->buildSqlString(
                        (new Delete('virtusers'))
                            ->where(['row_id' => $aliasId])
                    ),
                    $db::QUERY_MODE_EXECUTE
                );
                $this->alert()->good(
                    "Successfully deleted alias. " .
                    "<a href='{$refreshUrl}'>Click to continue</a>."
                );
                continue;
            }

            // Form is valid - transform the values into those expected by the database.
            $rawValues = $aliasForm->getData();
            $values = [
                'alias'   => $rawValues['alias' . $aliasId],
                'address' => $rawValues['address' . $aliasId],
                'groupid' => $groupId,
                'comment' => '',
            ];

            // Check if the alias has been used elsewhere.
            $conflictingAliases = $db->query(
                (new Sql($db))->buildSqlString(
                    (new Select())
                        ->from('virtusers')
                        ->where(function ($where) use ($aliasId, $values) {
                            $where
                                ->equalTo('alias', $values['alias'])
                                ->notEqualTo('row_id', $aliasId);
                        })
                ),
                []
            )->toArray();
            if (count($conflictingAliases) > 0) {
                $this->alert()->bad(
                    "{$values['alias']} is already in use, and cannot be duplicated."
                );
                continue;
            }

            if ($create) {
                $db->query(
                    (new Sql($db))->buildSqlString(
                        (new Insert('virtusers'))
                            ->values($values)
                    ),
                    $db::QUERY_MODE_EXECUTE
                );
                $this->alert()->good(
                    "Successfully added alias {$values['alias']}. " .
                    "<a href='{$refreshUrl}'>Click to continue</a>."
                );
            } else {
                $db->query(
                    (new Sql($db))->buildSqlString(
                        (new Update('virtusers'))
                            ->set($values)
                            ->where(['row_id' => $aliasId])
                    ),
                    $db::QUERY_MODE_EXECUTE
                );
                $this->alert()->good(
                    "Successfully updated alias {$values['alias']}. " .
                    "<a href='{$refreshUrl}'>Click to continue</a>."
                );
            }
        }

        return $viewModel;
    }

    public function domainsAction()
    {
        $this->layout()->title = 'Manage Group Domains';
        $db = $this->db;
        $authResponse = $this->auth()->ensureLevel(['admin']);
        if ($authResponse) {
            return $authResponse;
        }
        $refreshUrl = $this->currentUrl();

        $groupList = $this->arrayIndex(
            $db->query('SELECT id, groupname FROM scagroup ORDER BY groupname', []),
            'id',
            'groupname'
        );

        $viewModel = [
            'domainForms' => [],
        ];

                                                            //----------------------------------------------------------
                                                            // Build the domain forms
                                                            //----------------------------------------------------------
        $existingDomains = $db->query(
            (new Sql($db))->buildSqlString(
                (new Select())
                ->columns(['id', 'groupid', 'domain'])
                ->from('domains')
            ),
            []
        )->toArray();
        foreach ($existingDomains as $domain) {
            $domainId = $domain['id'];
            $domainForm = new Form\Group\Domain($domainId, true, $groupList);
            $domainForm->setData([
                'groupid' . $domainId => $domain['groupid'],
                'domain' . $domainId  => $domain['domain'],
            ]);
            $viewModel['domainForms'][$domainId] = $domainForm;
        }
        // Plus one to allow creating a new domain.
        $viewModel['domainForms']['new'] = new Form\Group\Domain('new', false, $groupList);

        $request = $this->getRequest();
        if (!$request->isPost()) {
            return $viewModel;
        }

                                                            //----------------------------------------------------------
                                                            // Process the domain forms
                                                            //----------------------------------------------------------
        $requestData = $request->getPost();
        foreach ($viewModel['domainForms'] as $domainId => $domainForm) {
            $create = isset($requestData['create' . $domainId]);
            $update = isset($requestData['update' . $domainId]);
            $delete = isset($requestData['delete' . $domainId]);

            if (!$create && !$update && !$delete) {
                // Nothing to do for this domain.
                continue;
            }

            $domainForm->setData($requestData);
            if (!$domainForm->isValid()) {
                // Report validation issues for each element.
                foreach ($domainForm->getMessages() as $element => $messages) {
                    $elementName = '';
                    if (strpos($element, 'groupid') === 0) {
                        $elementName = 'Group: ';
                    } elseif (strpos($element, 'domain') === 0) {
                        $elementName = 'Domain: ';
                    }

                    // Get first message value - only expect one as validator chain stops on first error.
                    $this->alert()->bad($elementName . reset($messages));
                }
                continue;
            }

            if ($create) {
                // Form is valid - transform the values into those expected by the database.
                $rawValues = $domainForm->getData();
                $values = [
                    'groupid' => $rawValues['groupid' . $domainId],
                    'domain'  => $rawValues['domain' . $domainId],
                ];

                // Check for an existing entry for the same domain and group.
                $conflictingDomains = false;
                foreach ($existingDomains as $domain) {
                    if ($domain['groupid'] == $values['groupid'] && $domain['domain'] == $values['domain']) {
                        $conflictingDomains = true;
                    }
                }
                if ($conflictingDomains) {
                    $this->alert()->bad('That group already has access to that domain.');
                    continue;
                }

                // All clear to create new entry.
                $db->query(
                    (new Sql($db))->buildSqlString(
                        (new Insert('domains'))
                            ->values($values)
                    ),
                    $db::QUERY_MODE_EXECUTE
                );
                $this->alert()->good(
                    "Successfully added domain {$values['domain']}. " .
                    "<a href='{$refreshUrl}'>Click to continue</a>."
                );
                continue;
            }

            // Find old data for this domain.
            $oldEntry = null;
            foreach ($existingDomains as $domain) {
                if ($domain['id'] == $domainId) {
                    $oldEntry = $domain;
                }
            }
            if ($oldEntry == null) {
                $this->alert()->bad('Unable to find domain to edit.');
                continue;
            }

            // Check for any aliases still using the old domain and group.
            $aliasCount = count($db->query(
                (new Sql($db))->buildSqlString(
                    (new Select())
                        ->from('virtusers')
                        ->where(function ($where) use ($oldEntry) {
                            $where
                                ->equalTo('groupid', $oldEntry['groupid'])
                                ->like('alias', '%@' . $oldEntry['domain'] . '.lochac.sca.org');
                        })
                ),
                []
            )->toArray());
            if ($aliasCount > 0) {
                $this->alert()->bad(
                    "That group has {$aliasCount} aliases under the domain {$oldEntry['domain']}.lochac.sca.org. " .
                    "Please remove these aliases or move them to a different domain before changing this domain."
                );
                continue;
            }

            // Form is valid - transform the values into those expected by the database.
            $rawValues = $domainForm->getData();
            $values = [
                'groupid' => $rawValues['groupid' . $domainId],
                'domain'  => $rawValues['domain' . $domainId],
            ];

            // Check for an existing entry for the same domain and group.
            if ($update) {
                $conflictingDomains = false;
                foreach ($existingDomains as $domain) {
                    if ($domain['groupid'] == $values['groupid'] && $domain['domain'] == $values['domain']) {
                        $conflictingDomains = true;
                    }
                }
                if ($conflictingDomains) {
                    $this->alert()->bad('That group already has access to that domain.');
                    continue;
                }
            }

            // All clear to update or delete.
            if ($update) {
                $db->query(
                    (new Sql($db))->buildSqlString(
                        (new Update('domains'))
                            ->set($values)
                            ->where(['id' => $domainId])
                    ),
                    $db::QUERY_MODE_EXECUTE
                );
                $this->alert()->good(
                    "Successfully updated domain {$values['domain']}. " .
                    "<a href='{$refreshUrl}'>Click to continue</a>."
                );
            } else {
                $db->query(
                    (new Sql($db))->buildSqlString(
                        (new Delete('domains'))
                            ->where(['id' => $domainId])
                    ),
                    $db::QUERY_MODE_EXECUTE
                );
                $this->alert()->good(
                    "Successfully deleted domain. " .
                    "<a href='{$refreshUrl}'>Click to continue</a>."
                );
            }
        }

        return $viewModel;
    }

    public function baronBaronessAction()
    {
        $this->layout()->title = 'Baron and Baroness Details';
        $db = $this->db;
        $authResponse = $this->auth()->ensureLevel(['admin', 'user']);
        if ($authResponse) {
            return $authResponse;
        }

        $groupList = $this->arrayIndex(
            $db->query("SELECT id, groupname FROM scagroup WHERE type='Barony' ORDER BY groupname", []),
            'id',
            'groupname'
        );

        // Reject non-Barony users.
        if ($this->auth()->getLevel() != 'admin' && !array_key_exists($this->auth()->getId(), $groupList)) {
            $this->alert()->bad('Available for baronies only.');
            return;
        }

                                                            //----------------------------------------------------------
                                                            // Group selection form
                                                            // Enabled for admin, disabled for regular groups
                                                            //----------------------------------------------------------
        $groupSelectForm = new Form\GroupSelect($groupList);

        $request = $this->getRequest();
        if ($this->auth()->getLevel() == 'admin') {
            $groupSelectForm->setData($request->getQuery());
        } else {
            $groupSelectForm->setData(['groupid' => $this->auth()->getId()]);
            $groupSelectForm->get('groupid')->setAttribute('disabled', true);
            $groupSelectForm->get('groupsubmit')->setAttribute('disabled', true);
        }

        $viewModel = [
            'groupSelectForm' => $groupSelectForm,
        ];

        if (!$groupSelectForm->isValid()) {
            return $viewModel;
        }

        $groupId = $groupSelectForm->getData()['groupid'];
                                                            //----------------------------------------------------------
                                                            // Build the report form
                                                            //----------------------------------------------------------
        $detailsForm = new Form\Group\Nobility();
        $viewModel['detailsForm'] = $detailsForm;

        $existingData = $db->query(
            (new Sql($db))->buildSqlString(
                (new Select())
                    ->from('barony')
                    ->where(['groupid' => $groupId])
            ),
            []
        )->toArray();
        $existingData = count($existingData) ? $existingData[0] : null;
        if ($existingData) {
            $detailsForm->setData([
                'baron' => array_intersect_key($existingData, array_flip([
                    'baronsca',
                    'baronreal',
                    'baronaddress',
                    'baronsuburb',
                    'baronstate',
                    'baronpostcode',
                    'baroncountry',
                    'baronphone',
                    'baronemail',
                ])),
                'baroness' => array_intersect_key($existingData, array_flip([
                    'baronesssca',
                    'baronessreal',
                    'same',
                    'baronessaddress',
                    'baronesssuburb',
                    'baronessstate',
                    'baronesspostcode',
                    'baronesscountry',
                    'baronessphone',
                    'baronessemail',
                ])),
            ]);
        }

                                                            //----------------------------------------------------------
                                                            // Process the submitted data
                                                            //----------------------------------------------------------
        if (!$request->isPost()) {
            return $viewModel;
        }

        $detailsForm->setData($request->getPost());
        if (!$detailsForm->isValid()) {
            return $viewModel;
        }

        $values = $detailsForm->getData();
        $fieldsToUpdate = $values['baron'] + $values['baroness'];

        if ($existingData) {
            $db->query(
                (new Sql($db))->buildSqlString(
                    (new Update('barony'))
                        ->set($fieldsToUpdate)
                        ->where(['groupid' => $groupId])
                ),
                $db::QUERY_MODE_EXECUTE
            );
            $this->alert()->good('Successfully updated nobility data for ' . $groupList[$groupId]);
        } else {
            $db->query(
                (new Sql($db))->buildSqlString(
                    (new Insert('barony'))
                        ->values(['groupid' => $groupId] + $fieldsToUpdate)
                ),
                $db::QUERY_MODE_EXECUTE
            );
            $this->alert()->good('Successfully created nobility data for ' . $groupList[$groupId]);
        }

        return $viewModel;
    }
}
