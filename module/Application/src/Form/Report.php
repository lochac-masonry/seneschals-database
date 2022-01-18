<?php

declare(strict_types=1);

namespace Application\Form;

use Laminas\Form\{Element, Fieldset, Form};
use Laminas\InputFilter\InputFilterProviderInterface;

class Report extends Form
{
    public function __construct($type, $email, $parentGroup, $groupOptions = [], $subgroups = [])
    {
        parent::__construct();

        $this->setAttribute('class', 'form--block');

                                                            //----------------------------------------------------------
                                                            // Section - general group details
                                                            //----------------------------------------------------------
        $this->add(
            new class ($groupOptions) extends Fieldset implements InputFilterProviderInterface
            {
                public function __construct($groupOptions = [])
                {
                    parent::__construct('groupDetails', []);

                    $this->setLabel('Group Details');

                    $this->add([
                        'type'    => 'text',
                        'name'    => 'groupname',
                        'options' => [
                            'label' => 'Name of Group',
                        ],
                        'attributes' => [
                            'size'     => 50,
                            'disabled' => true,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'text',
                        'name'    => 'website',
                        'options' => [
                            'label' => 'Group Website - contact Kingdom Seneschal to change',
                        ],
                        'attributes' => [
                            'size'     => 50,
                            'disabled' => true,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'select',
                        'name'    => 'type',
                        'options' => [
                            'label'         => 'Group Type',
                            'value_options' => [
                                'Kingdom'      => 'Kingdom',
                                'Principality' => 'Principality',
                                'Barony'       => 'Barony',
                                'Shire'        => 'Shire',
                                'Canton'       => 'Canton',
                                'College'      => 'College',
                            ],
                        ],
                        'attributes' => [
                            'disabled' => true,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'select',
                        'name'    => 'parentid',
                        'options' => [
                            'label'         => 'Parent Group',
                            'value_options' => $groupOptions,
                        ],
                        'attributes' => [
                            'disabled' => true,
                        ],
                    ]);
                }

                public function getInputFilterSpecification()
                {
                    // Disable validation for the disabled dropdowns.
                    return [
                        'type' => [
                            'required' => false,
                        ],
                        'parentid' => [
                            'required' => false,
                        ],
                    ];
                }
            }
        );

                                                            //----------------------------------------------------------
                                                            // Section - seneschal details
                                                            //----------------------------------------------------------
        $this->add(
            new class extends Fieldset implements InputFilterProviderInterface
            {
                public function __construct()
                {
                    parent::__construct('senDetails', []);

                    $this->setLabel(
                        'Seneschal Details - use Registry site or contact Kingdom Seneschal to make changes'
                    );

                    $this->add([
                        'type'    => 'text',
                        'name'    => 'sca_name',
                        'options' => [
                            'label' => 'SCA Name',
                        ],
                        'attributes' => [
                            'size'     => 50,
                            'disabled' => true,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'text',
                        'name'    => 'mundane_name',
                        'options' => [
                            'label' => 'Legal Name',
                        ],
                        'attributes' => [
                            'size'     => 50,
                            'disabled' => true,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'number',
                        'name'    => 'member',
                        'options' => [
                            'label' => 'Member Number',
                        ],
                        'attributes' => [
                            'disabled' => true,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'email',
                        'name'    => 'email',
                        'options' => [
                            'label' => 'Email Address',
                        ],
                        'attributes' => [
                            'size'     => 40,
                            'disabled' => true,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'text',
                        'name'    => 'start_date',
                        'options' => [
                            'label' => 'Warrant Start (YYYY-MM-DD)',
                        ],
                        'attributes' => [
                            'size'     => 10,
                            'disabled' => true,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'text',
                        'name'    => 'end_date',
                        'options' => [
                            'label' => 'Warrant End (YYYY-MM-DD)',
                        ],
                        'attributes' => [
                            'size'     => 10,
                            'disabled' => true,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'text',
                        'name'    => 'lastreport',
                        'options' => [
                            'label' => 'Last Report (YYYY-MM-DD)',
                        ],
                        'attributes' => [
                            'size'     => 10,
                            'disabled' => true,
                        ],
                    ]);
                }
            }
        );

                                                            //----------------------------------------------------------
                                                            // Section - CC recipient selection
                                                            //----------------------------------------------------------
        $this->add(
            new class ($email, $parentGroup) extends Fieldset implements InputFilterProviderInterface
            {
                public function __construct($email, $parentGroup)
                {
                    parent::__construct('copies', []);

                    $this->setLabel('Forward copies to:');

                    $this->add([
                        'type'    => 'checkbox',
                        'name'    => 'self',
                        'options' => [
                            'label'         => "Yourself via {$email}",
                            'label_options' => [
                                'label_position' => 'append',
                            ],
                        ],
                        'attributes' => [
                            'checked'  => true,
                            'disabled' => true,
                        ],
                    ]);
                    // Exclude Kingdom Seneschal, who receives reports via the reports deputy above.
                    if ($parentGroup['id'] !== 1) {
                        $this->add([
                            'type'    => 'checkbox',
                            'name'    => 'parent',
                            'options' => [
                                'label' =>
                                    "Seneschal of {$parentGroup['groupname']} " .
                                    "via {$parentGroup['email']}",
                                'label_options' => [
                                    'label_position' => 'append',
                                ],
                            ],
                            'attributes' => [
                                'checked'  => true,
                                'disabled' => true,
                            ],
                        ]);
                    }
                    $this->add([
                        'type'    => 'checkbox',
                        'name'    => 'kingdom',
                        'options' => [
                            'label'         => 'Kingdom Seneschal via reports@lochac.sca.org',
                            'label_options' => [
                                'label_position' => 'append',
                            ],
                        ],
                        'attributes' => [
                            'checked'  => true,
                            'disabled' => true,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'checkbox',
                        'name'    => 'copyhospit',
                        'options' => [
                            'label'         => 'Kingdom Hospitaller via hospitaller@lochac.sca.org',
                            'label_options' => [
                                'label_position' => 'append',
                            ],
                        ],
                        'attributes' => [],
                    ]);
                    $this->add([
                        'type'    => 'checkbox',
                        'name'    => 'copychirurgeon',
                        'options' => [
                            'label'         => 'Kingdom Chirurgeon via chirurgeon@lochac.sca.org',
                            'label_options' => [
                                'label_position' => 'append',
                            ],
                        ],
                        'attributes' => [],
                    ]);
                    $this->add([
                        'type'    => 'email',
                        'name'    => 'othercopy1',
                        'options' => [
                            'label' => 'Other Email',
                        ],
                        'attributes' => [],
                    ]);
                    $this->add([
                        'type'    => 'email',
                        'name'    => 'othercopy2',
                        'options' => [
                            'label' => 'Other Email',
                        ],
                        'attributes' => [],
                    ]);
                }

                public function getInputFilterSpecification()
                {
                    $othercopy1Spec = $this->get('othercopy1')->getInputSpecification();
                    $othercopy1Spec['required'] = false;
                    $othercopy2Spec = $this->get('othercopy2')->getInputSpecification();
                    $othercopy2Spec['required'] = false;
                    return [
                        'self' => [
                            'required' => false,
                        ],
                        'parent' => [
                            'required' => false,
                        ],
                        'kingdom' => [
                            'required' => false,
                        ],
                        'othercopy1' => $othercopy1Spec,
                        'othercopy2' => $othercopy2Spec,
                    ];
                }
            }
        );

                                                            //----------------------------------------------------------
                                                            // Section - report content
                                                            //----------------------------------------------------------
        $this->add(
            new class extends Fieldset implements InputFilterProviderInterface
            {
                public function __construct()
                {
                    parent::__construct('report', []);

                    $this->setLabel('Report Details');

                    $this->add([
                        'type'    => 'textarea',
                        'name'    => 'statistics',
                        'options' => [
                            'label' => 'Statistics: Total and Active Members, Total Funds',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 5,
                            'wrap' => 'virtual',
                        ],
                    ]);
                    $this->add([
                        'type'    => 'textarea',
                        'name'    => 'deputy',
                        'options' => [
                            'label' => '"Drop-dead" Deputy name, email and telephone',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 1,
                            'wrap' => 'virtual',
                        ],
                    ]);
                    $this->add([
                        'type'    => 'textarea',
                        'name'    => 'activities',
                        'options' => [
                            'label' => 'Summary of Regular Activities',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 10,
                            'wrap' => 'virtual',
                        ],
                    ]);
                    $this->add([
                        'type'    => 'textarea',
                        'name'    => 'achievements',
                        'options' => [
                            'label' => 'Special Achievements and Ideas that Worked',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 10,
                            'wrap' => 'virtual',
                        ],
                    ]);
                    $this->add([
                        'type'    => 'textarea',
                        'name'    => 'events',
                        'options' => [
                            'label' => 'Summary of Events',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 10,
                            'wrap' => 'virtual',
                        ],
                    ]);
                    $this->add([
                        'type'    => 'textarea',
                        'name'    => 'problems',
                        'options' => [
                            'label' => 'Problems of Note (please include names, not just "somebody went and...") ' .
                                '[in confidence]',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 10,
                            'wrap' => 'virtual',
                        ],
                    ]);
                    $this->add([
                        'type'    => 'textarea',
                        'name'    => 'questions',
                        'options' => [
                            'label' => 'Questions [in confidence]',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 10,
                            'wrap' => 'virtual',
                        ],
                    ]);
                    $this->add([
                        'type'    => 'textarea',
                        'name'    => 'plans',
                        'options' => [
                            'label' => 'Plans for the Future, Ideas, etc',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 10,
                            'wrap' => 'virtual',
                        ],
                    ]);
                    $this->add([
                        'type'    => 'textarea',
                        'name'    => 'comments',
                        'options' => [
                            'label' => 'General Comments',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 10,
                            'wrap' => 'virtual',
                        ],
                    ]);
                }

                public function getInputFilterSpecification()
                {
                    return [];
                }
            }
        );

                                                            //----------------------------------------------------------
                                                            // Section - officer reports
                                                            //----------------------------------------------------------
        $this->add(
            new class extends Fieldset implements InputFilterProviderInterface
            {
                public function __construct()
                {
                    parent::__construct('officers', []);

                    $this->setLabel("Brief SUMMARIES of Officer Reports (please don't cut-and-paste entire reports!)");

                    $this->add([
                        'type'    => 'textarea',
                        'name'    => 'summarshal',
                        'options' => [
                            'label' => 'Marshal',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 10,
                            'wrap' => 'virtual',
                        ],
                    ]);
                    $this->add([
                        'type'    => 'textarea',
                        'name'    => 'sumherald',
                        'options' => [
                            'label' => 'Herald',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 10,
                            'wrap' => 'virtual',
                        ],
                    ]);
                    $this->add([
                        'type'    => 'textarea',
                        'name'    => 'sumartssci',
                        'options' => [
                            'label' => 'Arts and Sciences',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 10,
                            'wrap' => 'virtual',
                        ],
                    ]);
                    $this->add([
                        'type'    => 'textarea',
                        'name'    => 'sumreeve',
                        'options' => [
                            'label' => 'Reeve',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 10,
                            'wrap' => 'virtual',
                        ],
                    ]);
                    $this->add([
                        'type'    => 'textarea',
                        'name'    => 'sumconstable',
                        'options' => [
                            'label' => 'Constable',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 10,
                            'wrap' => 'virtual',
                        ],
                    ]);
                    $this->add([
                        'type'    => 'textarea',
                        'name'    => 'sumchirurgeon',
                        'options' => [
                            'label' => 'Chirurgeon',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 10,
                            'wrap' => 'virtual',
                        ],
                    ]);
                    $this->add([
                        'type'    => 'textarea',
                        'name'    => 'sumchronicler',
                        'options' => [
                            'label' => 'Chronicler and/or Webminister',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 10,
                            'wrap' => 'virtual',
                        ],
                    ]);
                    $this->add([
                        'type'    => 'textarea',
                        'name'    => 'sumchatelaine',
                        'options' => [
                            'label' => 'Chatelaine/Hospitaller',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 10,
                            'wrap' => 'virtual',
                        ],
                    ]);
                    $this->add([
                        'type'    => 'textarea',
                        'name'    => 'others',
                        'options' => [
                            'label' => 'Other Officers (e.g. Lists, Youth Officer, DEI, Historian, ...)',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 10,
                            'wrap' => 'virtual',
                        ],
                    ]);
                }

                public function getInputFilterSpecification()
                {
                    return [];
                }
            }
        );

                                                            //----------------------------------------------------------
                                                            // Section - subgroups, if any
                                                            //----------------------------------------------------------
        $showHamlets = !in_array($type, ['Canton', 'College']);
        if (!empty($subgroups) || $showHamlets) {
            $this->add(
                new class ($subgroups, $showHamlets) extends Fieldset implements InputFilterProviderInterface
                {
                    public function __construct($subgroups, $showHamlets)
                    {
                        parent::__construct('subgroups', []);

                        $this->setLabel('Subgroups');

                        foreach ($subgroups as $subgroup) {
                            $this->add([
                                'type'    => 'textarea',
                                'name'    => 'subgroup' . $subgroup['id'],
                                'options' => [
                                    'label' => $subgroup['type'] . ' of ' . $subgroup['groupname'],
                                ],
                                'attributes' => [
                                    'cols' => 50,
                                    'rows' => 12,
                                    'wrap' => 'virtual',
                                ],
                            ]);
                        }

                        if ($showHamlets) {
                            $this->add([
                                'type'    => 'textarea',
                                'name'    => 'hamlets',
                                'options' => [
                                    'label' => 'Hamlets (if any)',
                                ],
                                'attributes' => [
                                    'cols' => 50,
                                    'rows' => 10,
                                    'wrap' => 'virtual',
                                ],
                            ]);
                        }
                    }

                    public function getInputFilterSpecification()
                    {
                        return [];
                    }
                }
            );
        }

        $this->add([
            'type'    => 'csrf',
            'name'    => 'csrf',
            'options' => [
                'csrf_options' => ['timeout' => 60 * 90],
            ],
            'attributes' => [],
        ]);

        $this->add([
            'type'       => 'submit',
            'name'       => 'submit',
            'options'    => [],
            'attributes' => [
                'value' => 'Submit',
            ],
        ]);
    }
}
