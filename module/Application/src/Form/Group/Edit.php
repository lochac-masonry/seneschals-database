<?php

namespace Application\Form\Group;

use Zend\Form\{Element, Fieldset, Form};
use Zend\InputFilter\InputFilterProviderInterface;

class Edit extends Form
{
    public function __construct($groupOptions = [])
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
                            'required' => true,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'text',
                        'name'    => 'area',
                        'options' => [
                            'label' => 'Description of Group Area',
                        ],
                        'attributes' => [
                            'size' => 50,
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
                        'attributes' => [],
                    ]);
                    $this->add([
                        'type'    => 'select',
                        'name'    => 'status',
                        'options' => [
                            'label'         => 'Group Status',
                            'value_options' => [
                                'live'     => 'live',
                                'dormant'  => 'dormant',
                                'abeyance' => 'abeyance',
                                'closed'   => 'closed',
                                'proposed' => 'proposed',
                            ],
                        ],
                        'attributes' => [],
                    ]);
                    $this->add([
                        'type'    => 'select',
                        'name'    => 'parentid',
                        'options' => [
                            'label'         => 'Parent Group',
                            'value_options' => $groupOptions,
                        ],
                        'attributes' => [],
                    ]);
                }

                public function getInputFilterSpecification()
                {
                    return [
                        'groupname' => [
                            'required' => true,
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
                            'size'     => 4,
                            'required' => true,
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
                        'type'    => 'date',
                        'name'    => 'warrantstart',
                        'options' => [
                            'label' => 'Warrant Start',
                        ],
                        'attributes' => [
                            'required' => true,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'date',
                        'name'    => 'warrantend',
                        'options' => [
                            'label' => 'Warrant End',
                        ],
                        'attributes' => [
                            'required' => true,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'date',
                        'name'    => 'lastreport',
                        'options' => [
                            'label' => 'Last Report',
                        ],
                        'attributes' => [
                            'required' => true,
                        ],
                    ]);
                }

                public function getInputFilterSpecification()
                {
                    return [
                        'scaname' => [
                            'required' => true,
                            'filters'  => [
                                ['name' => 'Zend\Filter\StringTrim'],
                            ],
                        ],
                        'realname' => [
                            'required' => true,
                            'filters'  => [
                                ['name' => 'Zend\Filter\StringTrim'],
                            ],
                        ],
                        'address' => [
                            'required' => true,
                            'filters'  => [
                                ['name' => 'Zend\Filter\StringTrim'],
                            ],
                        ],
                        'postcode' => [
                            'required' => true,
                            'filters'  => [
                                ['name' => 'Zend\Filter\StringTrim'],
                            ],
                        ],
                    ];
                }
            }
        );

        $this->add([
            'type'    => 'csrf',
            'name'    => 'csrf',
            'options' => [
                'csrf_options' => ['timeout' => 60 * 30],
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
