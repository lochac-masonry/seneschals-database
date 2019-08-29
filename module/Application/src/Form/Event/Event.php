<?php

namespace Application\Form\Event;

use Zend\Form\{Fieldset, Form};
use Zend\InputFilter\{InputFilter, InputFilterProviderInterface};
use Zend\Validator\ValidatorChain;

class Event extends Form
{
    public function __construct($groupOptions = [], $existing = false)
    {
        parent::__construct();

        $this->setAttribute('class', 'form--block');

                                                            //----------------------------------------------------------
                                                            // Section - general event details
                                                            //----------------------------------------------------------
        $this->add(new class($groupOptions) extends Fieldset implements InputFilterProviderInterface {
            public function __construct($groupOptions = [])
            {
                parent::__construct('eventGroup', []);

                $this->setLabel('Event Details');

                $this->add([
                    'type'    => 'text',
                    'name'    => 'name',
                    'options' => [
                        'label' => 'Name of Event',
                    ],
                    'attributes' => [
                        'required' => true,
                    ],
                ]);
                $this->add([
                    'type'    => 'select',
                    'name'    => 'groupid',
                    'options' => [
                        'label'         => 'Host Group',
                        'empty_option'  => 'Please select one...',
                        'value_options' => $groupOptions,
                    ],
                    'attributes' => [
                        'required' => true,
                    ],
                ]);
                $this->add([
                    'type'    => 'date',
                    'name'    => 'startdate',
                    'options' => [
                        'label' => 'Start Date',
                    ],
                    'attributes' => [
                        'required' => true,
                        'size'     => 10,
                    ],
                ]);
                $this->add([
                    'type'    => 'date',
                    'name'    => 'enddate',
                    'options' => [
                        'label' => 'End Date',
                    ],
                    'attributes' => [
                        'required' => true,
                        'size'     => 10,
                    ],
                ]);
                $this->add([
                    'type'    => 'textarea',
                    'name'    => 'setupTime',
                    'options' => [
                        'label' => 'Setup time(s), if applicable',
                    ],
                    'attributes' => [
                        'cols' => 50,
                        'rows' => 3,
                        'wrap' => 'virtual',
                    ],
                ]);
                $this->add([
                    'type'    => 'textarea',
                    'name'    => 'location',
                    'options' => [
                        'label' => 'Location (include Address)',
                    ],
                    'attributes' => [
                        'required' => true,
                        'cols'     => 50,
                        'rows'     => 3,
                        'wrap'     => 'virtual',
                    ],
                ]);
                $this->add([
                    'type'    => 'select',
                    'name'    => 'type',
                    'options' => [
                        'label'         => 'Event Type',
                        'empty_option'  => 'Please select one...',
                        'value_options' => [
                            'Feast'            => 'Feast',
                            'Tournament'       => 'Tournament',
                            'Collegium'        => 'Collegium',
                            'Crown Tournament' => 'Crown Tournament',
                            'Coronation'       => 'Coronation',
                            'Ball'             => 'Ball',
                            'War'              => 'War',
                            'Variety/Festival' => 'Variety/Festival',
                            'Other'            => 'Other',
                        ],
                    ],
                    'attributes' => [
                        'required' => true,
                    ],
                ]);
                $this->add([
                    'type'    => 'textarea',
                    'name'    => 'description',
                    'options' => [
                        'label' => 'Event Description/Details',
                    ],
                    'attributes' => [
                        'required' => true,
                        'cols'     => 50,
                        'rows'     => 10,
                        'wrap'     => 'virtual',
                    ],
                ]);
                $this->add([
                    'type'    => 'textarea',
                    'name'    => 'price',
                    'options' => [
                        'label' => 'Price - Include member, non-member and child prices',
                    ],
                    'attributes' => [
                        'required' => true,
                        'cols'     => 50,
                        'rows'     => 3,
                        'wrap'     => 'virtual',
                    ],
                ]);
            }

            public function getInputFilterSpecification()
            {
                return [
                    'name' => [
                        'required' => true,
                        'filters'  => [
                            ['name' => 'stringTrim'],
                        ],
                    ],
                    'setupTime' => [
                        'required' => false,
                        'filters'  => [
                            ['name' => 'stringTrim'],
                            ['name' => 'toNull'],
                        ],
                    ],
                    'location' => [
                        'required' => true,
                        'filters'  => [
                            ['name' => 'stringTrim'],
                        ],
                    ],
                    'description' => [
                        'required' => true,
                        'filters'  => [
                            ['name' => 'stringTrim'],
                        ],
                    ],
                    'price' => [
                        'required' => true,
                        'filters'  => [
                            ['name' => 'stringTrim'],
                        ],
                    ],
                ];
            }
        });

                                                            //----------------------------------------------------------
                                                            // Section - steward details
                                                            //----------------------------------------------------------
        $this->add(new class extends Fieldset implements InputFilterProviderInterface {
            public function __construct()
            {
                parent::__construct('stewardGroup', []);

                $this->setLabel('Steward Details');

                $this->add([
                    'type'    => 'text',
                    'name'    => 'stewardreal',
                    'options' => [
                        'label' => 'Legal Name (up to 32 characters, not published)',
                    ],
                    'attributes' => [
                        'required'  => true,
                        'maxlength' => 32,
                    ],
                ]);
                $this->add([
                    'type'    => 'text',
                    'name'    => 'stewardname',
                    'options' => [
                        'label' => 'SCA Name (up to 64 characters, published)',
                    ],
                    'attributes' => [
                        'required'  => true,
                        'maxlength' => 64,
                    ],
                ]);
                $this->add([
                    'type'    => 'text',
                    'name'    => 'stewardemail',
                    'options' => [
                        'label' => 'Email Address (up to 64 characters, published)',
                    ],
                    'attributes' => [
                        'required'  => true,
                        'maxlength' => 64,
                    ],
                ]);
            }

            public function getInputFilterSpecification()
            {
                return [
                    'stewardreal' => [
                        'required' => true,
                        'filters'  => [
                            ['name' => 'stringTrim'],
                        ],
                        'validators' => [
                            ['name' => 'stringLength', 'options' => ['max' => 32]],
                        ],
                    ],
                    'stewardname' => [
                        'required' => true,
                        'filters'  => [
                            ['name' => 'stringTrim'],
                        ],
                        'validators' => [
                            ['name' => 'stringLength', 'options' => ['max' => 64]],
                        ],
                    ],
                    'stewardemail' => [
                        'required' => true,
                        'filters'  => [
                            ['name' => 'stringTrim'],
                        ],
                        'validators' => [
                            ['name' => 'stringLength', 'options' => ['max' => 64]],
                        ],
                    ],
                ];
            }
        });

                                                            //----------------------------------------------------------
                                                            // Section - booking details
                                                            //----------------------------------------------------------
        $this->add(new class extends Fieldset implements InputFilterProviderInterface {
            public function __construct()
            {
                parent::__construct('bookingGroup', []);

                $this->setLabel('Booking Details - Leave blank if bookings not required');

                $this->add([
                    'type'    => 'textarea',
                    'name'    => 'bookingcontact',
                    'options' => [
                        'label' => 'Contact for Bookings (Name and Email address preferred)',
                    ],
                    'attributes' => [
                        'cols' => 50,
                        'rows' => 3,
                        'wrap' => 'virtual',
                    ],
                ]);
                $this->add([
                    'type'    => 'date',
                    'name'    => 'bookingsclose',
                    'options' => [
                        'label' => 'Date Bookings Close',
                    ],
                    'attributes' => [
                        'size'     => 10,
                    ],
                ]);
            }

            public function getInputFilterSpecification()
            {
                $bookingsCloseSpec = $this->get('bookingsclose')->getInputSpecification();
                $bookingsCloseSpec['required'] = false;
                $bookingsCloseSpec['filters'][] = ['name' => 'toNull'];
                return [
                    'bookingcontact' => [
                        'required' => false,
                        'filters'  => [
                            ['name' => 'stringTrim'],
                            ['name' => 'toNull'],
                        ],
                    ],
                    'bookingsclose' => $bookingsCloseSpec,
                ];
            }
        });

                                                            //----------------------------------------------------------
                                                            // Section - anti-spam and submit
                                                            //----------------------------------------------------------
        if (!$existing) {
            $this->add(new class extends Fieldset implements InputFilterProviderInterface {
                public function __construct()
                {
                    parent::__construct('endGroup', []);

                    $this->add([
                        'type'    => 'text',
                        'name'    => 'quiz',
                        'options' => [
                            'label' => 'Spam prevention: What is the name of this kingdom (one word)?',
                        ],
                        'attributes' => [
                            'required' => true,
                            'size'     => 10,
                        ],
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

                public function getInputFilterSpecification()
                {
                    return [
                        'quiz' => [
                            'required' => true,
                            'filters'  => [
                                ['name' => 'stringTrim'],
                                ['name' => 'stringToLower'],
                            ],
                            'validators' => [
                                ['name' => 'regex', 'options' => [
                                    'pattern' => '/^lochac$/',
                                    'message' => 'Incorrect',
                                ]],
                            ],
                        ],
                    ];
                }
            });
        }

                                                            //----------------------------------------------------------
                                                            // Section - approval and publicity options
                                                            //----------------------------------------------------------
        if ($existing) {
            $this->add(new class extends Fieldset implements InputFilterProviderInterface {
                public function __construct()
                {
                    parent::__construct('submitGroup', []);

                    $this->setLabel('Actions');

                    $this->add([
                        'type'    => 'radio',
                        'name'    => 'status',
                        'options' => [
                            'label'         => 'Change status to:',
                            'value_options' => [
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'new'      => 'New',
                            ],
                        ],
                        'attributes' => [
                            'required' => true,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'multiCheckbox',
                        'name'    => 'sendto',
                        'options' => [
                            'label'         => 'Also: (Can only post to Pegasus or Announce if approved)',
                            'value_options' => [
                                'pegasus'  => 'Advertise in Pegasus',
                                'calendar' => 'Update the Kingdom Calendar',
                                'announce' => 'Post on Lochac-Announce',
                            ],
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

                public function getInputFilterSpecification()
                {
                    $sendToSpec = $this->get('sendto')->getInputSpecification();
                    $sendToSpec['required'] = false;
                    return [
                        'sendto' => $sendToSpec,
                    ];
                }
            });
        }

        // Add cross-element validation as soon as the input data has been populated.
        $this->setInputFilter(new class extends InputFilter {
            public function setData($data)
            {
                parent::setData($data);

                // Validate rule: current date <= start date <= end date
                $startDate = $this->get('eventGroup')->get('startdate');
                $endDate = $this->get('eventGroup')->get('enddate');
                $startDate->getValidatorChain()->attachByName('greaterThan', [
                    'min'       => date('Y-m-d'),
                    'inclusive' => true,
                    'message'   => 'Start date is in the past.',
                ]);
                $endDate->getValidatorChain()->attachByName('greaterThan', [
                    'min'       => $startDate->getValue(),
                    'inclusive' => true,
                    'message'   => 'End date must be the same or later than the start date.',
                ]);

                // Validate rule: either set both booking close and booking contact, or neither of them
                $bookingContact = $this->get('bookingGroup')->get('bookingcontact');
                $bookingsClose = $this->get('bookingGroup')->get('bookingsclose');
                if ($bookingContact->getValue() && !$bookingsClose->getValue()) {
                    $bookingsClose->setRequired(true);
                    // Clear other validators to prevent messages about data format.
                    $bookingsClose->setValidatorChain((new ValidatorChain())->attachByName('notEmpty', [
                        'message' => 'Date is required as bookings contact was provided.',
                    ]));
                }
                if ($bookingsClose->getValue() && !$bookingContact->getValue()) {
                    $bookingContact->setRequired(true);
                    $bookingContact->getValidatorChain()->attachByName('notEmpty', [
                        'message' => 'Contact is required as bookings close date was provided.',
                    ]);
                }

                return $this;
            }
        });
    }
}
