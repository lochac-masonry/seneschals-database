<?php

declare(strict_types=1);

namespace Application\Form;

use Laminas\Form\{Element, Fieldset, Form};
use Laminas\InputFilter\InputFilterProviderInterface;

class Report extends Form
{
    public function __construct($groupOptions = [], $subgroups = [])
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
                            'label' => 'Group Website',
                        ],
                        'attributes' => [
                            'size' => 50,
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

                    $this->setLabel('Seneschal Details');

                    $this->add([
                        'type'    => 'text',
                        'name'    => 'scaname',
                        'options' => [
                            'label' => 'SCA Name',
                        ],
                        'attributes' => [
                            'size'     => 50,
                            'required' => true,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'text',
                        'name'    => 'realname',
                        'options' => [
                            'label' => 'Legal Name',
                        ],
                        'attributes' => [
                            'size'     => 50,
                            'required' => true,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'text',
                        'name'    => 'address',
                        'options' => [
                            'label' => 'Street Address',
                        ],
                        'attributes' => [
                            'size'     => 50,
                            'required' => true,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'text',
                        'name'    => 'suburb',
                        'options' => [
                            'label' => 'Suburb / Town',
                        ],
                        'attributes' => [
                            'size' => 20,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'select',
                        'name'    => 'state',
                        'options' => [
                            'label'         => 'State',
                            'value_options' => [
                                'ACT' => 'ACT',
                                'NSW' => 'NSW',
                                'NT'  => 'NT',
                                'QLD' => 'QLD',
                                'SA'  => 'SA',
                                'TAS' => 'TAS',
                                'VIC' => 'VIC',
                                'WA'  => 'WA',
                                'NZ'  => 'Not Applicable (NZ)',
                            ],
                        ],
                        'attributes' => [],
                    ]);
                    $this->add([
                        'type'    => 'text',
                        'name'    => 'postcode',
                        'options' => [
                            'label' => 'Postcode',
                        ],
                        'attributes' => [
                            'size' => 4,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'select',
                        'name'    => 'country',
                        'options' => [
                            'label'         => 'Country',
                            'value_options' => [
                                'AU' => 'Australia',
                                'NZ' => 'New Zealand',
                            ],
                        ],
                        'attributes' => [],
                    ]);
                    $this->add([
                        'type'    => 'text',
                        'name'    => 'phone',
                        'options' => [
                            'label' => 'Phone',
                        ],
                        'attributes' => [
                            'size' => 15,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'email',
                        'name'    => 'email',
                        'options' => [
                            'label' => 'Email Address - Published on group listing',
                        ],
                        'attributes' => [
                            'size'     => 40,
                            'required' => true,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'number',
                        'name'    => 'memnum',
                        'options' => [
                            'label' => 'Member Number',
                        ],
                        'attributes' => [
                            'required' => true,
                            'step'     => 1,
                            'min'      => 1,
                            'max'      => 999999,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'text',
                        'name'    => 'warrantstart',
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
                        'name'    => 'warrantend',
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

                public function getInputFilterSpecification()
                {
                    return [
                        'scaname' => [
                            'required' => true,
                            'filters'  => [
                                ['name' => 'Laminas\Filter\StringTrim'],
                            ],
                        ],
                        'realname' => [
                            'required' => true,
                            'filters'  => [
                                ['name' => 'Laminas\Filter\StringTrim'],
                            ],
                        ],
                        'address' => [
                            'required' => true,
                            'filters'  => [
                                ['name' => 'Laminas\Filter\StringTrim'],
                            ],
                        ],
                    ];
                }
            }
        );

                                                            //----------------------------------------------------------
                                                            // Section - CC recipient selection
                                                            //----------------------------------------------------------
        $this->add(
            new class extends Fieldset implements InputFilterProviderInterface
            {
                public function __construct()
                {
                    parent::__construct('copies', []);

                    $this->setLabel('Forward copies to:');

                    $this->add([
                        'type'    => 'checkbox',
                        'name'    => 'copyhospit',
                        'options' => [
                            'label' => 'Kingdom Hospitaller',
                        ],
                        'attributes' => [],
                    ]);
                    $this->add([
                        'type'    => 'checkbox',
                        'name'    => 'copychirurgeon',
                        'options' => [
                            'label' => 'Kingdom Chirurgeon',
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
                            'rows' => 2,
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
                            'label' => 'Problems of Note',
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
                            'label' => 'Questions',
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

                    $this->setLabel('Summary of Officer Reports');

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
                        'name'    => 'sumlists',
                        'options' => [
                            'label' => 'Lists',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 10,
                            'wrap' => 'virtual',
                        ],
                    ]);
                    $this->add([
                        'type'    => 'textarea',
                        'name'    => 'sumyouth',
                        'options' => [
                            'label' => 'Youth Officer',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 10,
                            'wrap' => 'virtual',
                        ],
                    ]);
                    $this->add([
                        'type'    => 'textarea',
                        'name'    => 'sumhistorian',
                        'options' => [
                            'label' => 'Historian',
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
        if (!empty($subgroups)) {
            $this->add(
                new class ($subgroups) extends Fieldset implements InputFilterProviderInterface
                {
                    public function __construct($subgroups)
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
                'csrf_options' => ['timeout' => 60 * 60],
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

        $reset = new Element('reset');
        $reset->setAttributes([
            'type'  => 'reset',
            'value' => 'Reset',
        ]);
        $this->add($reset);
    }
}
