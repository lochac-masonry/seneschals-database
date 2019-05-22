<?php

class SenDb_Form_Event_Edit extends Zend_Form
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
                'validators' => array('date')
            )
        );
        $this->addElement(
            'text',
            'enddate',
            array(
                'label'      => 'End Date (YYYY-MM-DD)',
                'required'   => true,
                'size'       => 10,
                'validators' => array('date')
            )
        );
        $this->addElement(
            'textarea',
            'setupTime',
            array(
                'label'    => 'Setup time(s), if applicable',
                'rows'     => 3,
                'cols'     => 50,
                'wrap'     => 'virtual',
                'filters'    => array('stringTrim')
            )
        );
        $this->addElement(
            'textarea',
            'location',
            array(
                'label'    => 'Location (include Address)',
                'required' => true,
                'rows'     => 3,
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
                'setupTime',
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
                'required' => true,
                'validators' => array(
                    array(
                        'stringLength',
                        false,
                        array(0, 32)
                    )
                )
            )
        );
        $this->addElement(
            'text',
            'stewardname',
            array(
                'label'    => 'SCA Name (published)',
                'required' => true,
                'validators' => array(
                    array(
                        'stringLength',
                        false,
                        array(0, 32)
                    )
                )
            )
        );
        $this->addElement(
            'text',
            'stewardemail',
            array(
                'label'      => 'Email Address (published)',
                'required'   => true,
                'validators' => array(
                    'emailAddress',
                    array(
                        'stringLength',
                        false,
                        array(0, 64)
                    )
                )
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
                'rows'    => 3,
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
                                                            // Section - approval and publicity options
                                                            //----------------------------------------------------------
        $this->addElement(
            'radio',
            'status',
            array(
                'label'        => 'Change status to:',
                'multiOptions' => array(
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    'new'      => 'New'
                )
            )
        );
        $this->addElement(
            'multiCheckbox',
            'sendto',
            array(
                'label'        => 'Also: (Can only post to Pegasus or Announce if approved)',
                'multiOptions' => array(
                    'pegasus'  => 'Advertise in Pegasus',
                    'calendar' => 'Update the Kingdom Calendar',
                    'announce' => 'Post on Lochac-Announce'
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
        $this->addElement(
            'text',
            'googleid',
            array(
                'hidden' => true
            )
        );
        $this->addDisplayGroup(
            array(
                'status',
                'sendto',
                'submit',
                'googleid'
            ),
            'submitGroup',
            array('legend' => 'Actions')
        );

    }

}
