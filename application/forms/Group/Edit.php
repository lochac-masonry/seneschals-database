<?php

class SenDb_Form_Group_Edit extends Zend_Form
{
    public function init()
    {
        $this->setAction('#');

                                                            //----------------------------------------------------------
                                                            // Section - general group details
                                                            //----------------------------------------------------------
        $this->addElement(
            'text',
            'groupname',
            array(
                'label'    => 'Name of Group',
                'size'     => 50,
                'required' => true
            )
        );
        $this->addElement(
            'text',
            'area',
            array(
                'label' => 'Description of Group Area',
                'size'  => 50
            )
        );
        $this->addElement(
            'text',
            'website',
            array(
                'label' => 'Group Website',
                'size'  => 50
            )
        );
        $this->addElement(
            'select',
            'type',
            array(
                'label'        => 'Group Type',
                'multiOptions' => array(
                    'Kingdom'      => 'Kingdom',
                    'Principality' => 'Principality',
                    'Barony'       => 'Barony',
                    'Shire'        => 'Shire',
                    'Canton'       => 'Canton',
                    'College'      => 'College'
                )
            )
        );
        $this->addElement(
            'select',
            'status',
            array(
                'label'        => 'Group Status',
                'multiOptions' => array(
                    'live'     => 'live',
                    'dormant'  => 'dormant',
                    'abeyance' => 'abeyance',
                    'closed'   => 'closed',
                    'proposed' => 'proposed'
                )
            )
        );
        $this->addElement(
            'select',
            'parentid',
            array(
                'label' => 'Parent Group'
            )
        );
        $this->addDisplayGroup(
            array(
                'groupname',
                'area',
                'website',
                'type',
                'status',
                'parentid'
            ),
            'groupDetails',
            array('legend' => 'Group Details')
        );

                                                            //----------------------------------------------------------
                                                            // Section - seneschal's details
                                                            //----------------------------------------------------------
        $this->addElement(
            'text',
            'scaname',
            array(
                'label'    => 'SCA Name',
                'size'     => 50,
                'required' => true
            )
        );
        $this->addElement(
            'text',
            'realname',
            array(
                'label'    => 'Legal Name',
                'size'     => 50,
                'required' => true
            )
        );
        $this->addElement(
            'text',
            'address',
            array(
                'label'    => 'Street Address',
                'size'     => 50,
                'required' => true
            )
        );
        $this->addElement(
            'text',
            'suburb',
            array(
                'label' => 'Suburb / Town',
                'size'  => 20
            )
        );
        $this->addElement(
            'select',
            'state',
            array(
                'label'        => 'State',
                'multiOptions' => array(
                    'ACT' => 'ACT',
                    'NSW' => 'NSW',
                    'NT'  => 'NT',
                    'QLD' => 'QLD',
                    'SA'  => 'SA',
                    'TAS' => 'TAS',
                    'VIC' => 'VIC',
                    'WA'  => 'WA',
                    'NZ'  => 'Not Applicable (NZ)'
                )
            )
        );
        $this->addElement(
            'text',
            'postcode',
            array(
                'label' => 'Postcode',
                'size'  => 4
            )
        );
        $this->addElement(
            'select',
            'country',
            array(
                'label'        => 'Country',
                'multiOptions' => array(
                    'AU' => 'Australia',
                    'NZ' => 'New Zealand'
                )
            )
        );
        $this->addElement(
            'text',
            'phone',
            array(
                'label' => 'Phone',
                'size'  => 15
            )
        );
        $this->addElement(
            'text',
            'email',
            array(
                'label'      => 'Email Address - Published on group listing',
                'size'       => 40,
                'required'   => true,
                'filters'    => array('stringTrim'),
                'validators' => array('emailAddress')
            )
        );
        $this->addElement(
            'text',
            'memnum',
            array(
                'label'      => 'Member Number',
                'size'       => 6,
                'required'   => true,
                'validators' => array('digits')
            )
        );
        $this->addElement(
            'text',
            'warrantstart',
            array(
                'label'      => 'Warrant Start (YYYY-MM-DD)',
                'size'       => 10,
                'validators' => array('date')
            )
        );
        $this->addElement(
            'text',
            'warrantend',
            array(
                'label'      => 'Warrant End (YYYY-MM-DD)',
                'size'       => 10,
                'validators' => array('date')
            )
        );
        $this->addElement(
            'text',
            'lastreport',
            array(
                'label'      => 'Last Report (YYYY-MM-DD)',
                'size'       => 10,
                'validators' => array('date')
            )
        );
        $this->addDisplayGroup(
            array(
                'scaname',
                'realname',
                'address',
                'suburb',
                'state',
                'postcode',
                'country',
                'phone',
                'email',
                'memnum',
                'warrantstart',
                'warrantend',
                'lastreport'
            ),
            'senDetails',
            array('legend' => 'Seneschal Details')
        );

        $this->addElement(
            'submit',
            'submit',
            array(
                'label' => 'Submit'
            )
        );
        $this->addElement(
            'submit',
            'reset',
            array(
                'label' => 'Reset'
            )
        );

    }

}
