<?php

namespace Application\Form\Postcode;

use Zend\Form\{Element, Fieldset, Form};
use Zend\InputFilter\InputFilterProviderInterface;

class Query extends Form
{
    public function __construct($groupOptions = [])
    {
        parent::__construct();

        $this->setAttribute('class', 'form--block');

        $this->add([
            'type'    => 'checkbox',
            'name'    => 'printable',
            'options' => [
                'label' => 'Printable Report?',
            ],
            'attributes' => [],
        ]);

                                                            //----------------------------------------------------------
                                                            // Section - query by group
                                                            //----------------------------------------------------------
        $this->add(
            new class ($groupOptions) extends Fieldset implements InputFilterProviderInterface
            {
                public function __construct($groupOptions = [])
                {
                    parent::__construct('byGroup', []);

                    $this->setLabel('Search by Group');

                    $this->add([
                        'type'    => 'select',
                        'name'    => 'group',
                        'options' => [
                            'label'         => 'Group name:',
                            'value_options' => $groupOptions,
                        ],
                        'attributes' => [],
                    ]);
                    $this->add([
                        'type'       => 'submit',
                        'name'       => 'querybygroup',
                        'options'    => [],
                        'attributes' => [
                            'value' => 'Submit',
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
                                                            // Section - query by postcode
                                                            //----------------------------------------------------------
        $this->add(
            new class extends Fieldset implements InputFilterProviderInterface
            {
                public function __construct()
                {
                    parent::__construct('byCode', []);

                    $this->setLabel('Search by Postcode');

                    $this->add([
                        'type'    => 'number',
                        'name'    => 'postcode',
                        'options' => [
                            'label' => 'Postcode:',
                        ],
                        'attributes' => [
                            'step' => 1,
                            'min'  => 1,
                            'max'  => 9999,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'submit',
                        'name'    => 'querybycode',
                        'options' => [],
                        'attributes' => [
                            'value' => 'Submit',
                        ],
                    ]);
                }

                public function getInputFilterSpecification()
                {
                    $postcodeSpec = $this->get('postcode')->getInputSpecification();
                    $postcodeSpec['required'] = false;
                    return [
                        'postcode' => $postcodeSpec,
                    ];
                }
            }
        );

                                                            //----------------------------------------------------------
                                                            // Section - query by postcode range
                                                            //----------------------------------------------------------
        $this->add(
            new class extends Fieldset implements InputFilterProviderInterface
            {
                public function __construct()
                {
                    parent::__construct('byRange', []);

                    $this->setLabel('Search by Postcode Range');

                    $this->add([
                        'type'    => 'number',
                        'name'    => 'rangestart',
                        'options' => [
                            'label' => 'Range Start:',
                        ],
                        'attributes' => [
                            'step' => 1,
                            'min'  => 1,
                            'max'  => 9999,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'number',
                        'name'    => 'rangeend',
                        'options' => [
                            'label' => 'Range End:',
                        ],
                        'attributes' => [
                            'step' => 1,
                            'min'  => 1,
                            'max'  => 9999,
                        ],
                    ]);
                    $this->add([
                        'type'       => 'submit',
                        'name'       => 'querybyrange',
                        'options'    => [],
                        'attributes' => [
                            'value' => 'Submit',
                        ],
                    ]);
                }

                public function getInputFilterSpecification()
                {
                    $rangestartSpec = $this->get('rangestart')->getInputSpecification();
                    $rangestartSpec['required'] = false;
                    $rangeendSpec = $this->get('rangeend')->getInputSpecification();
                    $rangeendSpec['required'] = false;
                    return [
                        'rangestart' => $rangestartSpec,
                        'rangeend'   => $rangeendSpec,
                    ];
                }
            }
        );

                                                            //----------------------------------------------------------
                                                            // Section - query by locality
                                                            //----------------------------------------------------------
        $this->add(
            new class extends Fieldset implements InputFilterProviderInterface
            {
                public function __construct()
                {
                    parent::__construct('byLocality', []);

                    $this->setLabel('Search by Suburb Name');

                    $this->add([
                        'type'    => 'text',
                        'name'    => 'locality',
                        'options' => [
                            'label' => 'Suburb/Locality Name:',
                        ],
                        'attributes' => [
                            'maxlength' => 64,
                        ],
                    ]);
                    $this->add([
                        'type'       => 'submit',
                        'name'       => 'querybylocality',
                        'options'    => [],
                        'attributes' => [
                            'value' => 'Submit',
                        ],
                    ]);
                }

                public function getInputFilterSpecification()
                {
                    return [];
                }
            }
        );

        $reset = new Element('reset');
        $reset->setAttributes([
            'type'  => 'reset',
            'value' => 'Reset',
        ]);
        $this->add($reset);
    }
}
