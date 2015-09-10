<?php

class SenDb_Form_Event_New extends Zend_Form
{
    public function init()
    {
        $this->setAction('#');

                                                            //----------------------------------------------------------
                                                            // Section - general event details
                                                            //----------------------------------------------------------
        $this->addElement(
            'text',
            'name',
            array(
                'label'    => 'Name of Event',
                'required' => true
            )
        );
        $this->addElement(
            'select',
            'groupid',
            array(
                'label' => 'Host Group'
            )
        );
        $this->addElement(
            'text',
            'startdate',
            array(
                'label'      => 'Start Date (YYYY-MM-DD)',
                'required'   => true,
                'size'       => 10,
                'validators' => array('date'),
                'class'      => 'start date'
            )
        );
        $this->addElement(
            'text',
            'starttime',
            array(
                'label'      => 'Start Time',
                'required'   => true,
                'size'       => 10,
                'class'      => 'start time'
            )
        );
        $this->addElement(
            'text',
            'enddate',
            array(
                'label'      => 'End Date (YYYY-MM-DD)',
                'required'   => true,
                'size'       => 10,
                'validators' => array('date'),
                'class'      => 'end date'
            )
        );
        $this->addElement(
            'text',
            'endtime',
            array(
                'label'      => 'End Time',
                'required'   => true,
                'size'       => 10,
                'class'      => 'end time'
            )
        );
        $this->addElement(
            'textarea',
            'location',
            array(
                'label'    => 'Location (include Address)',
                'required' => true,
                'rows'     => 2,
                'cols'     => 50,
                'wrap'     => 'virtual'
            )
        );
        $this->addElement(
            'select',
            'type',
            array(
                'label'        => 'Event Type',
                'multiOptions' => array(
                    'Feast'            => 'Feast',
                    'Tournament'       => 'Tournament',
                    'Collegium'        => 'Collegium',
                    'Crown Tournament' => 'Crown Tournament',
                    'Coronation'       => 'Coronation',
                    'Ball'             => 'Ball',
                    'War'              => 'War',
                    'Variety/Festival' => 'Variety/Festival',
                    'Other'            => 'Other'
                )
            )
        );
        $this->addElement(
            'textarea',
            'description',
            array(
                'label'    => 'Event Description/Details',
                'required' => true,
                'rows'     => 10,
                'cols'     => 50,
                'wrap'     => 'virtual'
            )
        );
        $this->addElement(
            'textarea',
            'price',
            array(
                'label'    => 'Price - Include member, non-member and child prices',
                'required' => true,
                'rows'     => 3,
                'cols'     => 50,
                'wrap'     => 'virtual'
            )
        );
        $this->addDisplayGroup(
            array(
                'name',
                'groupid',
                'startdate',
                'enddate',
                'location',
                'type',
                'description',
                'price'
            ),
            'eventGroup',
            array('legend' => 'Event Details')
        );

                                                            //----------------------------------------------------------
                                                            // Section - steward details
                                                            //----------------------------------------------------------
        $this->addElement(
            'text',
            'stewardreal',
            array(
                'label'    => 'Legal Name (not published)',
                'required' => true
            )
        );
        $this->addElement(
            'text',
            'stewardname',
            array(
                'label'    => 'SCA Name (published)',
                'required' => true
            )
        );
        $this->addElement(
            'text',
            'stewardemail',
            array(
                'label'      => 'Email Address (published)',
                'required'   => true,
                'validators' => array('emailAddress')
            )
        );
        $this->addDisplayGroup(
            array(
                'stewardreal',
                'stewardname',
                'stewardemail'
            ),
            'stewardGroup',
            array('legend' => 'Steward Details')
        );

                                                            //----------------------------------------------------------
                                                            // Section - booking details
                                                            //----------------------------------------------------------
        $this->addElement(
            'textarea',
            'bookingcontact',
            array(
                'label'   => 'Contact for Bookings (Name and Email address preferred)',
                'filters' => array('stringTrim'),
                'rows'    => 2,
                'cols'    => 50,
                'wrap'    => 'virtual'
            )
        );
        $this->addElement(
            'text',
            'bookingsclose',
            array(
                'label'      => 'Date Bookings Close (YYYY-MM-DD)',
                'size'       => 10,
                'filters'    => array('stringTrim'),
                'validators' => array('date')
            )
        );
        $this->addDisplayGroup(
            array(
                'bookingcontact',
                'bookingsclose'
            ),
            'bookingGroup',
            array('legend' => 'Booking Details - Leave blank if bookings not required')
        );

                                                            //----------------------------------------------------------
                                                            // Section - anti-spam and submit
                                                            //----------------------------------------------------------
        $this->addElement(
            'text',
            'quiz',
            array(
                'label'      => 'Spam prevention: What is the name of this kingdom (one word)?',
                'required'   => true,
                'size'       => 10,
                'filters'    => array('stringToLower'),
                'validators' => array(
                    array(
                        'regex',
                        false,
                        array(
                            'pattern'  => '/^lochac$/',
                            'messages' => array('regexNotMatch' => 'Incorrect')
                        )
                    )
                )
            )
        );
        $this->addElement(
            'submit',
            'submit',
            array(
                'label' => 'Submit'
            )
        );
        $this->addDisplayGroup(
            array(
                'quiz',
                'submit'
            ),
            'endGroup'
        );

    }

}
