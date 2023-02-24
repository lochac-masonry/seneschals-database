<?php

declare(strict_types=1);

namespace Application\Form\Group;

use Laminas\Form\{Element, Fieldset, Form};
use Laminas\InputFilter\InputFilterProviderInterface;

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
                        'type'    => 'text',
                        'name'    => 'emailDomain',
                        'options' => [
                            'label' => 'Domain name for officer email addresses',
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

                    $this->setLabel(
                        'Seneschal Details (taken from the Regnumator, log into the Registry to make changes)'
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
                        'type'    => 'text',
                        'name'    => 'start_date',
                        'options' => [
                            'label' => 'Warrant Start',
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
                            'label' => 'Warrant End',
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
                        'member' => [
                            'required' => false,
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
