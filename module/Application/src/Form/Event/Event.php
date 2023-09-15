<?php

declare(strict_types=1);

namespace Application\Form\Event;

use Laminas\Form\{Fieldset, Form};
use Laminas\InputFilter\{InputFilter, InputFilterProviderInterface};
use Laminas\Validator\ValidatorChain;

class Event extends Form
{
    public function __construct($groupOptions = [], $existing = false, $attachments = [])
    {
        parent::__construct();

        $this->setAttribute('class', 'form--block');

                                                            //----------------------------------------------------------
                                                            // Section - general event details
                                                            //----------------------------------------------------------
        $this->add(
            new class ($groupOptions) extends Fieldset implements InputFilterProviderInterface
            {
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
                            'required'  => true,
                            'maxlength' => 64,
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
                        'name'    => 'timetable',
                        'options' => [
                            'label' => 'Timetable, if available - including setup time',
                        ],
                        'attributes' => [
                            'cols' => 50,
                            'rows' => 5,
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
                    $this->add([
                        'type'    => 'url',
                        'name'    => 'website',
                        'options' => [
                            'label' => 'Event Website (optional)',
                        ],
                        'attributes' => [
                            'size' => 50,
                        ],
                    ]);
                    $this->add([
                        'type'    => 'checkbox',
                        'name'    => 'notifyInsurer',
                        'options' => [
                            'label' =>
                                "This event's total income (before costs) is likely to be " .
                                'over $5,000 so please notify the SCA NZ insurer to ensure coverage',
                            'label_options' => [
                                'label_position' => 'append',
                            ],
                        ],
                        'attributes' => [],
                    ]);
                }

                public function getInputFilterSpecification()
                {
                    $websiteSpec = $this->get('website')->getInputSpecification();
                    $websiteSpec['required'] = false;
                    $websiteSpec['filters'][] = ['name' => 'toNull'];

                    return [
                        'name' => [
                            'required' => true,
                            'filters'  => [
                                ['name' => 'stringTrim'],
                            ],
                            'validators' => [
                                ['name' => 'stringLength', 'options' => ['max' => 64]],
                            ],
                        ],
                        'timetable' => [
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
                        'website' => $websiteSpec,
                        'notifyInsurer' => [
                            'required' => false,
                        ],
                    ];
                }
            }
        );

                                                            //----------------------------------------------------------
                                                            // Section - steward details
                                                            //----------------------------------------------------------
        $this->add(
            new class extends Fieldset implements InputFilterProviderInterface
            {
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
            }
        );

                                                            //----------------------------------------------------------
                                                            // Section - booking details
                                                            //----------------------------------------------------------
        $this->add(
            new class extends Fieldset implements InputFilterProviderInterface
            {
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
            }
        );

                                                            //----------------------------------------------------------
                                                            // Section - attachments
                                                            //----------------------------------------------------------
        $this->add(
            new class ($attachments) extends Fieldset implements InputFilterProviderInterface
            {
                private $acceptedFileTypes = [
                    'pdf'  => 'application/pdf',
                    'doc'  => 'application/msword',
                    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'odt'  => 'application/vnd.oasis.opendocument.text',
                    'xls'  => 'application/vnd.ms-excel',
                    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'ods'  => 'application/vnd.oasis.opendocument.spreadsheet',
                    'ppt'  => 'application/vnd.ms-powerpoint',
                    'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'odp'  => 'application/vnd.oasis.opendocument.presentation',
                ];

                public function __construct($attachments)
                {
                    parent::__construct('attachments', []);

                    $this->setLabel('Attachments - including Risk assessment forms for AU stewards');

                    foreach ($attachments as $attachment) {
                        $this->add(new AttachmentFieldset($attachment));
                    }

                    $this->add([
                        'type'       => 'file',
                        'name'       => 'files',
                        'options'    => [
                            'label' => 'Maximum 1MB per file, 3MB total. Common document formats only. ' .
                                'Filename must not contain apostrophes/single quotes.',
                        ],
                        'attributes' => [
                            'accept'   => $this->getAcceptAttribute(),
                            'multiple' => true,
                        ],
                    ]);
                }

                public function getInputFilterSpecification()
                {
                    $filesSpec = $this->get('files')->getInputSpecification();
                    $filesSpec['validators'] = [
                        // In practice, total size is limited by the php.ini value upload_max_filesize.
                        ['name' => 'fileFilesSize', 'options' => ['max' => '3MB']],
                        ['name' => 'fileSize', 'options' => ['max' => '1MB']],
                        ['name' => 'fileExtension', 'options' => ['extension' => $this->getAcceptedExtensions()]],
                        ['name' => 'fileMimeType', 'options' => ['mimeType' => $this->getAcceptedMimeTypes()]],
                    ];
                    $filesSpec['filters'] = [
                        ['name' => 'fileRenameUpload', 'options' => [
                            'target'    => './data/files/',
                            'randomize' => true
                        ]],
                    ];
                    return [
                        'files' => $filesSpec,
                    ];
                }

                private function getAcceptedExtensions()
                {
                    return array_keys($this->acceptedFileTypes);
                }

                private function getAcceptedMimeTypes()
                {
                    return array_values($this->acceptedFileTypes);
                }

                private function getAcceptAttribute()
                {
                    $prefixDot = function ($ext) {
                        return '.' . $ext;
                    };
                    return implode(
                        ',',
                        array_merge(
                            array_map($prefixDot, $this->getAcceptedExtensions()),
                            $this->getAcceptedMimeTypes()
                        )
                    );
                }
            }
        );

        if (!$existing) {
                                                            //----------------------------------------------------------
                                                            // Section - anti-spam and submit
                                                            //----------------------------------------------------------
            $this->add(
                new class extends Fieldset implements InputFilterProviderInterface
                {
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
                }
            );
        }

                                                            //----------------------------------------------------------
                                                            // Section - approval and publicity options
                                                            //----------------------------------------------------------
        if ($existing) {
            $this->add(
                new class extends Fieldset implements InputFilterProviderInterface
                {
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

        // Add cross-element validation as soon as the input data has been populated.
        $this->setInputFilter(
            new class extends InputFilter
            {
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
            }
        );
    }
}
