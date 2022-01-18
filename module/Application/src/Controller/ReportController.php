<?php

declare(strict_types=1);

namespace Application\Controller;

use Application\Form;
use Laminas\Db\Sql\{Join, Select, Sql, Update};

class ReportController extends DatabaseController
{
    private function sendReportEmails($reportData, $groupData, $parentGroupEmail, $subgroups)
    {
        $mailsubj  = 'Report from the ' . $groupData['type'] . ' of ' . $groupData['groupname'];

        $mailbody  = $mailsubj
                   . "\nDate: " . $reportData['senDetails']['lastreport']
                   . "\nSubmitted by: " . $groupData['sca_name']
                   . " (" . $groupData['mundane_name'] . ")"
                   . "\nWarrant Ends: " . $groupData['end_date']
                   . "\n"
                   . "\nSTATISTICS"
                   . "\n==========\n"
                   . $reportData['report']['statistics']
                   . "\n"
                   . "\nDEPUTY"
                   . "\n==========\n"
                   . $reportData['report']['deputy']
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
                   . "\n\n== Others\n" . $reportData['officers']['others']
                   . "\n"
                   . "\nSUMMARY OF SUB-GROUPS"
                   . "\n======================";

        foreach ($subgroups as $subgroup) {
            $mailbody .= "\nSummary report for " . $subgroup['type'] . " of " . $subgroup['groupname'];
            $mailbody .= "\n" . $reportData['subgroups']['subgroup' . $subgroup['id']] . "\n";
        }

        if (isset($reportData['subgroups']) && isset($reportData['subgroups']['hamlets'])) {
            $mailbody .= "\nHamlets:\n" . $reportData['subgroups']['hamlets'] . "\n";
        }

        $mailto[] = $parentGroupEmail;
        $mailto[] = $groupData['email'];
        $mailto[] = "reports@lochac.sca.org";
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

                                                            //----------------------------------------------------------
                                                            // Send report
                                                            //----------------------------------------------------------
        if ($this->sendEmail($mailto, $mailsubj, $mailbody, 'reports@lochac.sca.org')) {
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
        $aliasesQuery = [];
        $sampleRoute = null;

        $request = $this->getRequest();
        if ($this->auth()->getLevel() == 'admin') {
            $groupSelectForm->setData($request->getQuery());
        } else {
            $groupSelectForm->setData(['groupid' => $this->auth()->getId()]);
            $groupSelectForm->get('groupid')->setAttribute('disabled', true);
            $groupSelectForm->get('groupsubmit')->setAttribute('disabled', true);
        }

        if ($groupSelectForm->isValid()) {
            $groupId = $groupSelectForm->getData()['groupid'];
            if ($this->auth()->getLevel() == 'admin') {
                $aliasesQuery['groupid'] = $groupId;
            }
                                                            //----------------------------------------------------------
                                                            // Build the report form
                                                            //----------------------------------------------------------
            $initialData = (array) $db->query(
                (new Sql($db))->buildSqlString(
                    (new Select())
                        ->columns(['groupname', 'website', 'type', 'parentid', 'email', 'lastreport'])
                        ->from('scagroup')
                        ->join(
                            'warrants',
                            'warrants.scagroup = scagroup.id',
                            ['sca_name', 'mundane_name', 'member', 'start_date', 'end_date'],
                            Join::JOIN_LEFT_OUTER
                        )
                        ->where([
                            'scagroup.id'     => $groupId,
                            'warrants.office' => 1, // Seneschal
                            '(warrants.start_date <= CURDATE() OR warrants.start_date IS NULL)',
                            '(warrants.end_date >= CURDATE() OR warrants.end_date IS NULL)',
                        ])
                ),
                []
            )->current();
            $parentGroup = $db->query(
                (new Sql($db))->buildSqlString(
                    (new Select())
                        ->columns(['id', 'groupname', 'email'])
                        ->from('scagroup')
                        ->where(['id' => $initialData['parentid']])
                ),
                []
            )->current();
            $subgroupSql = "SELECT id, type, groupname FROM scagroup " .
                           "WHERE parentid = ? " .
                           "AND (status = 'live' OR status = 'proposed')";
            $subgroups = $db->query($subgroupSql, [$groupId])->toArray();

            $memberCountSql = 'SELECT SUM(tally) AS memberCount FROM membership_stats WHERE groupname = ?';
            $memberCount = $db->query(
                $memberCountSql,
                [$initialData['groupname']]
            )->toArray()[0]['memberCount'] ?? '???';
            $statisticsTemplate =
                "Members (from Registry): {$memberCount}\n" .
                "Active members this quarter (est): ??\n" .
                "Active non-members this quarter (est): ??\n" .
                "Total funds in bank (from your latest Reeve report): $???";

            $detailsForm = new Form\Report(
                $initialData['type'],
                $initialData['email'],
                $parentGroup,
                $groupList,
                $subgroups
            );

            $detailsForm->setData([
                'groupDetails' => array_intersect_key($initialData, array_flip([
                    'groupname',
                    'website',
                    'type',
                    'parentid',
                ])),
                'senDetails' => array_intersect_key($initialData, array_flip([
                    'sca_name',
                    'mundane_name',
                    'member',
                    'email',
                    'start_date',
                    'end_date',
                    'lastreport',
                ])),
                'report' => [
                    'statistics' => $statisticsTemplate,
                ],
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
                        'memnum',
                    ]));

                    $updateResult = $db->query(
                        (new Sql($db))->buildSqlString(
                            (new Update('scagroup'))
                                ->set($fieldsToUpdate)
                                ->where(['id' => $groupId])
                        ),
                        $db::QUERY_MODE_EXECUTE
                    );

                    $this->alert($updateResult->getAffectedRows() . ' row(s) updated.');

                    $this->sendReportEmails($values, $initialData, $parentGroup->email, $subgroups);
                }
            }

            switch ($initialData['type']) {
                case 'Barony':
                    $sampleRoute = 'report/sample-barony';
                    break;
                case 'Canton':
                    $sampleRoute = 'report/sample-canton';
                    break;
                case 'College':
                    $sampleRoute = 'report/sample-college';
                    break;
                case 'Shire':
                    $sampleRoute = 'report/sample-shire';
                    break;
            }
        }

        return [
            'groupSelectForm' => $groupSelectForm,
            'detailsForm'     => $detailsForm,
            'aliasesQuery'    => $aliasesQuery,
            'sampleRoute'     => $sampleRoute,
        ];
    }

    public function sampleBaronyAction()
    {
        $this->layout()->title = 'Quarterly Report Sample - Barony';
        return [];
    }

    public function sampleCantonAction()
    {
        $this->layout()->title = 'Quarterly Report Sample - Canton';
        return [];
    }

    public function sampleCollegeAction()
    {
        $this->layout()->title = 'Quarterly Report Sample - College';
        return [];
    }

    public function sampleShireAction()
    {
        $this->layout()->title = 'Quarterly Report Sample - Shire';
        return [];
    }
}
