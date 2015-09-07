<?php

class SenDb_Form_Group_Nobility extends Zend_Form
{
    public function init()
    {
        $this->setAction('#');

                                                            //----------------------------------------------------------
                                                            // Section - Baron details
                                                            //----------------------------------------------------------
        $this->addElement(
            'text',
            'baronsca',
            array(
                'label' => 'SCA Name',
                'size'  => 50
            )
        );
        $this->addElement(
            'text',
            'baronreal',
            array(
                'label' => 'Legal Name',
                'size'  => 50
            )
        );
        $this->addElement(
            'text',
            'baronaddress',
            array(
                'label' => 'Street Address',
                'size'  => 50
            )
        );
        $this->addElement(
            'text',
            'baronsuburb',
            array(
                'label' => 'Suburb / Town',
                'size'  => 20
            )
        );
        $this->addElement(
            'select',
            'baronstate',
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
            'baronpostcode',
            array(
                'label' => 'Postcode',
                'size'  => 4
            )
        );
        $this->addElement(
            'select',
            'baroncountry',
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
            'baronphone',
            array(
                'label' => 'Phone',
                'size'  => 15
            )
        );
        $this->addElement(
            'text',
            'baronemail',
            array(
                'label'      => 'Email Address',
                'size'       => 30,
                'filters'    => array('stringTrim'),
                'validators' => array('emailAddress')
            )
        );
        $this->addDisplayGroup(
            array(
                'baronsca',
                'baronreal',
                'baronaddress',
                'baronsuburb',
                'baronstate',
                'baronpostcode',
                'baroncountry',
                'baronphone',
                'baronemail'
            ),
            'baron',
            array('legend' => "Baron's Details")
        );

                                                            //----------------------------------------------------------
                                                            // Section - Baroness details
                                                            //----------------------------------------------------------
        $this->addElement(
            'text',
            'baronesssca',
            array(
                'label' => 'SCA Name',
                'size'  => 50
            )
        );
        $this->addElement(
            'text',
            'baronessreal',
            array(
                'label' => 'Legal Name',
                'size'  => 50
            )
        );
        $this->addElement(
            'checkbox',
            'same',
            array(
                'label' => 'Address same as for Baron?'
            )
        );
        $this->addElement(
            'text',
            'baronessaddress',
            array(
                'label' => 'Street Address',
                'size'  => 50
            )
        );
        $this->addElement(
            'text',
            'baronesssuburb',
            array(
                'label' => 'Suburb / Town',
                'size'  => 20
            )
        );
        $this->addElement(
            'select',
            'baronessstate',
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
            'baronesspostcode',
            array(
                'label' => 'Postcode',
                'size'  => 4
            )
        );
        $this->addElement(
            'select',
            'baronesscountry',
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
            'baronessphone',
            array(
                'label' => 'Phone',
                'size'  => 15
            )
        );
        $this->addElement(
            'text',
            'baronessemail',
            array(
                'label'      => 'Email Address',
                'size'       => 30,
                'filters'    => array('stringTrim'),
                'validators' => array('emailAddress')
            )
        );
        $this->addDisplayGroup(
            array(
                'baronesssca',
                'baronessreal',
                'same',
                'baronessaddress',
                'baronesssuburb',
                'baronessstate',
                'baronesspostcode',
                'baronesscountry',
                'baronessphone',
                'baronessemail'
            ),
            'baroness',
            array('legend' => "Baroness' Details")
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
