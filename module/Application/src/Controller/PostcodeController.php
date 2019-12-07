<?php

namespace Application\Controller;

use Application\Form;
use Zend\Db\Sql\{Delete, Expression, Select, Sql, Update};
use Zend\View\Model\ViewModel;

class PostcodeController extends DatabaseController
{
    public function listAction()
    {
        $sql = "SELECT DISTINCT a.postcode AS postcode, a.state AS state, " .
               "b.groupname AS groupname FROM postcode a JOIN scagroup b " .
               "ON a.groupid=b.id ORDER BY a.postcode, a.state";

        return (new ViewModel([
            'postcodeResultSet' => $this->db->query($sql, [])->toArray(),
        ]))->setTerminal(true);
    }

    public function queryAction()
    {
        $this->layout()->title = 'Postcode Query';
        $db = $this->db;

        $groupList = $this->arrayIndex(
            $db->query('SELECT id, groupname FROM scagroup ORDER BY groupname', []),
            'id',
            'groupname'
        );

                                                            //----------------------------------------------------------
                                                            // Build postcode query form
                                                            //----------------------------------------------------------
        $form = new Form\Postcode\Query($groupList);

                                                            //----------------------------------------------------------
                                                            // Process form - attempt to assign postcode range
                                                            //----------------------------------------------------------
        $request = $this->getRequest();
        $resultSet = null;
        $printable = false;
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $values = $form->getData();

                $printable = $form->get('printable')->isChecked();

                // Prepare basic SQL statement common to all query types.
                $select = (new Select())
                    ->quantifier('DISTINCT')
                    ->columns([
                        'postcode',
                        'localities' => new Expression("GROUP_CONCAT(locality ORDER BY locality ASC SEPARATOR ', ')"),
                        'state',
                    ])
                    ->from('postcode')
                    ->join('scagroup', 'scagroup.id = postcode.groupid', ['groupname'])
                    ->group(['postcode', 'state', 'groupname']);

                // Filter the SQL statement based on the selected query type.
                if (isset($values['byGroup']['querybygroup'])) {
                    $select->where(['groupid' => $values['byGroup']['group']]);
                } elseif (isset($values['byCode']['querybycode'])) {
                    $select->where(['postcode.postcode' => $values['byCode']['postcode']]);
                } elseif (isset($values['byRange']['querybyrange'])) {
                    $select->where(function ($where) use ($values) {
                        $where->between(
                            'postcode.postcode',
                            $values['byRange']['rangestart'],
                            $values['byRange']['rangeend']
                        );
                    });
                } elseif (isset($values['byLocality']['querybylocality'])) {
                    $select->where(function ($where) use ($values) {
                        $where->like('locality', "%{$values['byLocality']['locality']}%");
                    });
                } else {
                    // No query selected.
                    return [
                        'form'      => $form,
                        'resultSet' => $resultSet,
                        'printable' => false,
                    ];
                }

                // Execute the query.
                $resultSet = $db->query(
                    (new Sql($db))->buildSqlString($select),
                    []
                )->toArray();
            }
        }

        return (new ViewModel([
            'form'      => $form,
            'resultSet' => $resultSet,
            'printable' => $printable,
        ]))->setTerminal($printable);
    }

    public function assignAction()
    {
        $this->layout()->title = 'Assign Postcodes';
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
                                                            // Build postcode assignment form
                                                            //----------------------------------------------------------
        $form = new Form\Postcode\Assign($groupList);

                                                            //----------------------------------------------------------
                                                            // Process form - attempt to assign postcode range
                                                            //----------------------------------------------------------
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $values = $form->getData()['assign'];

                $result = $db->query(
                    (new Sql($db))->buildSqlString(
                        (new Update('postcode'))
                            ->set(['groupid' => $values['group']])
                            ->where(function ($where) use ($values) {
                                $where->between('postcode', $values['rangestart'], $values['rangeend']);
                            })
                    ),
                    $db::QUERY_MODE_EXECUTE
                );

                $this->alert()->good($result->getAffectedRows() . ' row(s) updated.');
            }
        }

        return [
            'form' => $form,
        ];
    }

    public function uploadAction()
    {
        $this->layout()->title = 'Upload Postcodes File';
        $db = $this->db;
        $authResponse = $this->auth()->ensureLevel(['admin']);
        if ($authResponse) {
            return $authResponse;
        }

        $form = new Form\Postcode\Upload();

                                                            //----------------------------------------------------------
                                                            // Process the submitted file
                                                            //----------------------------------------------------------
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData(array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            ));
            if ($form->isValid()) {
                $filename = $form->getData()['upload']['userfile']['tmp_name'];
                $file = fopen($filename, 'r');

                // Mark all of the existing postcode records as old, and initialise counters.
                $db->query(
                    (new Sql())->buildSqlString(
                        (new Update('postcode'))
                            ->set(['current' => 'N'])
                    ),
                    $db::QUERY_MODE_EXECUTE
                );
                $updateCount = 0;
                $insertCount = 0;
                $deleteCount = 0;
                $errorCount = 0;

                // Grab the first row to use as headings.
                if (!feof($file)) {
                    $headRow = fgetcsv($file);
                }

                // Get each row in turn
                while (!feof($file)) {
                    $rowData = fgetcsv($file);

                    // add header row as keys for row array
                    foreach ($rowData as $key => $value) {
                        $row[$headRow[$key]] = $value;
                    }

                    $sql = 'SELECT COUNT(*) as existing FROM postcode ' .
                        'WHERE postcode = ? AND locality = ? AND state = ?';
                    $existing = $db->query(
                        $sql,
                        [$row['Pcode'], $row['Locality'], $row['State']]
                    )->toArray()[0]['existing'];

                    // Does the entry exist?
                    if ($existing === 0) {
                        // Find the group that has the postcode and add to db.
                        $result = $db->query(
                            'SELECT groupid FROM postcode WHERE postcode = ? LIMIT 1',
                            [$row['Pcode']]
                        )->toArray();
                        // Default to Lochac if not previously assigned.
                        $groupId = count($result) > 0 && $result[0]['groupid'] != 0 ? $result[0]['groupid'] : 1;

                        try {
                            $result = $db->query(
                                (new Sql())->buildSqlString(
                                    (new Insert('postcode'))
                                        ->values([
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
                                            'groupid'          => $groupId,
                                        ])
                                ),
                                $db::QUERY_MODE_EXECUTE
                            );
                            $insertCount += $result->getAffectedRows();
                        } catch (Exception $e) {
                            $this->alert()->bad(
                                "Possible error adding {$row[Locality]}, {$row[Pcode]}, {$row[State]}."
                            );
                            $errorCount++;
                        }
                    } else {
                        // Update with current details.
                        try {
                            $result = $db->query(
                                (new Sql($db))->buildSqlString(
                                    (new Update('postcode'))
                                        ->set([
                                            'current'          => 'Y',
                                            'comments'         => $row['Comments'],
                                            'deliveryoffice'   => $row['DeliveryOffice'],
                                            'presortindicator' => $row['PresortIndicator'],
                                            'parcelzone'       => $row['ParcelZone'],
                                            'bspnumber'        => $row['BSPnumber'],
                                            'bspname'          => $row['BSPname'],
                                            'category'         => $row['Category'],
                                        ])
                                        ->where([
                                            'postcode' => $row['Pcode'],
                                            'locality' => $row['Locality'],
                                            'state'    => $row['State'],
                                        ])
                                ),
                                $db::QUERY_MODE_EXECUTE
                            );
                            $updateCount += $result->getAffectedRows();
                        } catch (Exception $e) {
                            $this->alert()->bad(
                                "Possible error updating {$row[Locality]}, {$row[Pcode]}, {$row[State]}."
                            );
                            $errorCount++;
                        }
                    }
                }

                // Delete any entries not in the uploaded file
                try {
                    $result = $db->query(
                        (new Sql($db))->buildSqlString(
                            (new Delete('postcode'))
                                ->where(['current' => 'N'])
                        ),
                        $db::QUERY_MODE_EXECUTE
                    );
                    $deleteCount += $result->getAffectedRows();
                } catch (Exception $e) {
                    $this->alert()->bad('Possible error deleting old entries.');
                    $errorCount++;
                }
                $this->alert($insertCount . ' row(s) added.');
                $this->alert($updateCount . ' row(s) updated.');
                $this->alert($deleteCount . ' row(s) deleted.');
                $this->alert($errorCount . ' error(s) encountered.');

                fclose($file);
            }
        }

        return [
            'uploadForm' => $form,
        ];
    }
}
