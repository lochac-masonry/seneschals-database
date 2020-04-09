<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Form;
use Laminas\Db\Sql\{Select, Sql, Update};

class ReportController extends DatabaseController
{
    private function sendReportEmails($reportData, $groupData, $parentGroupEmail, $subgroups)
    {
        $mailsubj  = 'Report from the ' . $groupData['type'] . ' of ' . $groupData['groupname'];

        $mailbody  = $mailsubj
                   . "\nDate: " . $reportData['senDetails']['lastreport']
                   . "\nSubmitted by: " . $reportData['senDetails']['scaname']
                   . " (" . $reportData['senDetails']['realname'] . ")"
                   . "\n"
                   . "\nSTATISTICS"
                   . "\n==========\n"
                   . $reportData['report']['statistics']
                   . "\n"
                   . "\nREGULAR ACTIVITIES"
                   . "\n==================\n"
                   . $reportData['report']['activities']
                   . "\n"
                   . "\nACHIEVEMENTS"
                   . "\n============\n"
                   . $reportData['report']['achievements']
                   . "\n"
                   . "\nEVENTS"
                   . "\n======\n"
                   . $reportData['report']['events']
                   . "\n"
                   . "\nPROBLEMS"
                   . "\n========\n"
                   . $reportData['report']['problems']
                   . "\n"
                   . "\nQUESTIONS"
                   . "\n=========\n"
                   . $reportData['report']['questions']
                   . "\n"
                   . "\nPLANS"
                   . "\n=====\n"
                   . $reportData['report']['plans']
                   . "\n"
                   . "\nGENERAL COMMENTS"
                   . "\n================\n"
                   . $reportData['report']['comments']
                   . "\n"
                   . "\nSUMMARY OF OFFICERS"
                   . "\n==================="
                   . "\n\n== Marshal\n" . $reportData['officers']['summarshal']
                   . "\n\n== Herald\n" . $reportData['officers']['sumherald']
                   . "\n\n== Arts and Sciences\n" . $reportData['officers']['sumartssci']
                   . "\n\n== Reeve\n" . $reportData['officers']['sumreeve']
                   . "\n\n== Constable\n" . $reportData['officers']['sumconstable']
                   . "\n\n== Chirurgeon\n" . $reportData['officers']['sumchirurgeon']
                   . "\n\n== Chronicler/Webminister\n" . $reportData['officers']['sumchronicler']
                   . "\n\n== Chatelaine/Hospitaller\n" . $reportData['officers']['sumchatelaine']
                   . "\n\n== Lists\n" . $reportData['officers']['sumlists']
                   . "\n\n== Youth\n" . $reportData['officers']['sumyouth']
                   . "\n\n== Historian\n" . $reportData['officers']['sumhistorian']
                   . "\n"
                   . "\nSUMMARY OF SUB-GROUPS"
                   . "\n======================";

        foreach ($subgroups as $subgroup) {
            $mailbody .= "\nSummary report for " . $subgroup['type'] . " of " . $subgroup['groupname'];
            $mailbody .= "\n" . $reportData['subgroups']['subgroup' . $subgroup['id']] . "\n";
        }

        $mailto[] = $parentGroupEmail;
        $mailto[] = $reportData['senDetails']['email'];
        if ($reportData['copies']['copyhospit']) {
            $mailto[] = "hospitaller@lochac.sca.org";
        }
        if ($reportData['copies']['copychirurgeon']) {
            $mailto[] = "chirurgeon@lochac.sca.org";
        }
        if (!empty($reportData['copies']['othercopy1'])) {
            $mailto[] = $reportData['copies']['othercopy1'];
        }
        if (!empty($reportData['copies']['othercopy2'])) {
            $mailto[] = $reportData['copies']['othercopy2'];
        }

        $mailheaders = "From: " . $reportData['senDetails']['email'];

                                                            //----------------------------------------------------------
                                                            // Send report
                                                            //----------------------------------------------------------
        if ($this->sendEmail($mailto, $mailsubj, $mailbody, $mailheaders)) {
            $this->alert()->good('Report sent to ' . count($mailto) . ' recipient(s).');
        } else {
            $this->alert()->bad('Failed to send report.');
        }
    }

    public function indexAction()
    {
        $this->layout()->title = 'Submit Quarterly Report';
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
        $groupSelectForm = new Form\GroupSelect($groupList);
        $detailsForm = null;

        $request = $this->getRequest();
        if ($this->auth()->getLevel() == 'admin') {
            $groupSelectForm->setData($request->getQuery());
        } else {
            $groupSelectForm->setData(['groupid' => $this->auth()->getId()]);
            $groupSelectForm->get('groupid')->setAttribute('disabled', true);
            $groupSelectForm->get('submit')->setAttribute('disabled', true);
        }

        if ($groupSelectForm->isValid()) {
            $groupId = $groupSelectForm->getData()['groupid'];
                                                            //----------------------------------------------------------
                                                            // Build the report form
                                                            //----------------------------------------------------------
            $initialData = (array) $db->query(
                (new Sql($db))->buildSqlString(
                    (new Select())
                        ->from('scagroup')
                        ->where(['id' => $groupId])
                ),
                []
            )->current();
            $parentGroupEmail = $db->query(
                (new Sql($db))->buildSqlString(
                    (new Select())
                        ->columns(['email'])
                        ->from('scagroup')
                        ->where(['id' => $initialData['parentid']])
                ),
                []
            )->current()->email;
            $subgroupSql = "SELECT id, type, groupname FROM scagroup " .
                           "WHERE parentid = ? " .
                           "AND (status = 'live' OR status = 'proposed')";
            $subgroups = $db->query($subgroupSql, [$groupId])->toArray();

            $detailsForm = new Form\Report($groupList, $subgroups);

            $detailsForm->setData([
                'groupDetails' => array_intersect_key($initialData, array_flip([
                    'groupname',
                    'website',
                    'type',
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

                                                            //----------------------------------------------------------
                                                            // Process the submitted report
                                                            //----------------------------------------------------------
            if ($request->isPost()) {
                $detailsForm->setData($request->getPost());

                if ($detailsForm->isValid()) {
                    $values = $detailsForm->getData();

                                                            //----------------------------------------------------------
                                                            // Update database with latest group details
                                                            //----------------------------------------------------------
                    $values['senDetails']['lastreport'] = date('Y-m-d');
                    $fieldsToUpdate = array_intersect_key($values['senDetails'], array_flip([
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
                        'memnum',
                    ]));
                    $fieldsToUpdate['website'] = $values['groupDetails']['website'];

                    $updateResult = $db->query(
                        (new Sql($db))->buildSqlString(
                            (new Update('scagroup'))
                                ->set($fieldsToUpdate)
                                ->where(['id' => $groupId])
                        ),
                        $db::QUERY_MODE_EXECUTE
                    );

                    $this->alert($updateResult->getAffectedRows() . ' row(s) updated.');

                    $this->sendReportEmails($values, $initialData, $parentGroupEmail, $subgroups);
                }
            }
        }

        return [
            'groupSelectForm' => $groupSelectForm,
            'detailsForm'     => $detailsForm,
        ];
    }
}
