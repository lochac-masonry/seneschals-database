<?php

namespace Application\Form\Group;

use Zend\Form\{Element, Fieldset, Form};
use Zend\InputFilter\InputFilterProviderInterface;

class Nobility extends Form
{
    public function __construct()
    {
        parent::__construct();

        $this->setAttribute('class', 'form--block');

                                                            //----------------------------------------------------------
                                                            // Section - Baron details
                                                            //----------------------------------------------------------
        $this->add(new class extends Fieldset implements InputFilterProviderInterface {
            public function __construct()
            {
                parent::__construct('baron', []);

                $this->setLabel("Baron's Details");

                $this->add([
                    'type'    => 'text',
                    'name'    => 'baronsca',
                    'options' => [
                        'label' => 'SCA Name',
                    ],
                    'attributes' => [
                        'size' => 50,
                    ],
                ]);
                $this->add([
                    'type'    => 'text',
                    'name'    => 'baronreal',
                    'options' => [
                        'label' => 'Legal Name',
                    ],
                    'attributes' => [
                        'size' => 50,
                    ],
                ]);
                $this->add([
                    'type'    => 'text',
                    'name'    => 'baronaddress',
                    'options' => [
                        'label' => 'Street Address',
                    ],
                    'attributes' => [
                        'size' => 50,
                    ],
                ]);
                $this->add([
                    'type'    => 'text',
                    'name'    => 'baronsuburb',
                    'options' => [
                        'label' => 'Suburb / Town',
                    ],
                    'attributes' => [
                        'size' => 20,
                    ],
                ]);
                $this->add([
                    'type'    => 'select',
                    'name'    => 'baronstate',
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
                    'name'    => 'baronpostcode',
                    'options' => [
                        'label' => 'Postcode',
                    ],
                    'attributes' => [
                        'size' => 4,
                    ],
                ]);
                $this->add([
                    'type'    => 'select',
                    'name'    => 'baroncountry',
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
                    'name'    => 'baronphone',
                    'options' => [
                        'label' => 'Phone',
                    ],
                    'attributes' => [
                        'size' => 15,
                    ],
                ]);
                $this->add([
                    'type'    => 'email',
                    'name'    => 'baronemail',
                    'options' => [
                        'label' => 'Email Address',
                    ],
                    'attributes' => [
                        'size' => 30,
                    ],
                ]);
            }

            public function getInputFilterSpecification()
            {
                $emailSpec = $this->get('baronemail')->getInputSpecification();
                $emailSpec['required'] = false;
                return [
                    'baronemail' => $emailSpec,
                ];
            }
        });

                                                            //----------------------------------------------------------
                                                            // Section - Baroness details
                                                            //----------------------------------------------------------
        $this->add(new class extends Fieldset implements InputFilterProviderInterface {
            public function __construct()
            {
                parent::__construct('baroness', []);

                $this->setLabel("Baroness' Details");

                $this->add([
                    'type'    => 'text',
                    'name'    => 'baronesssca',
                    'options' => [
                        'label' => 'SCA Name',
                    ],
                    'attributes' => [
                        'size' => 50,
                    ],
                ]);
                $this->add([
                    'type'    => 'text',
                    'name'    => 'baronessreal',
                    'options' => [
                        'label' => 'Legal Name',
                    ],
                    'attributes' => [
                        'size' => 50,
                    ],
                ]);
                $this->add([
                    'type'    => 'checkbox',
                    'name'    => 'same',
                    'options' => [
                        'label' => 'Address same as for Baron?',
                    ],
                    'attributes' => [],
                ]);
                $this->add([
                    'type'    => 'text',
                    'name'    => 'baronessaddress',
                    'options' => [
                        'label' => 'Street Address',
                    ],
                    'attributes' => [
                        'size' => 50,
                    ],
                ]);
                $this->add([
                    'type'    => 'text',
                    'name'    => 'baronesssuburb',
                    'options' => [
                        'label' => 'Suburb / Town',
                    ],
                    'attributes' => [
                        'size' => 20,
                    ],
                ]);
                $this->add([
                    'type'    => 'select',
                    'name'    => 'baronessstate',
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
                    'name'    => 'baronesspostcode',
                    'options' => [
                        'label' => 'Postcode',
                    ],
                    'attributes' => [
                        'size' => 4,
                    ],
                ]);
                $this->add([
                    'type'    => 'select',
                    'name'    => 'baronesscountry',
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
                    'name'    => 'baronessphone',
                    'options' => [
                        'label' => 'Phone',
                    ],
                    'attributes' => [
                        'size' => 15,
                    ],
                ]);
                $this->add([
                    'type'    => 'email',
                    'name'    => 'baronessemail',
                    'options' => [
                        'label' => 'Email Address',
                    ],
                    'attributes' => [
                        'size' => 30,
                    ],
                ]);
            }

            public function getInputFilterSpecification()
            {
                $emailSpec = $this->get('baronessemail')->getInputSpecification();
                $emailSpec['required'] = false;
                return [
                    'baronessemail' => $emailSpec,
                ];
            }
        });

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
