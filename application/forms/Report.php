<?php

class SenDb_Form_Report extends Zend_Form
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
                'disabled' => true
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
                'disabled'     => true,
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
            'parentid',
            array(
                'label'    => 'Parent Group',
                'disabled' => true
            )
        );
        $this->addDisplayGroup(
            array(
                'groupname',
                'website',
                'type',
                'parentid'
            ),
            'groupDetails',
            array('legend' => 'Group Details')
        );

                                                            //----------------------------------------------------------
                                                            // Section - seneschal details
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
                'label'    => 'Warrant Start (YYYY-MM-DD)',
                'size'     => 10,
                'disabled' => true
            )
        );
        $this->addElement(
            'text',
            'warrantend',
            array(
                'label'    => 'Warrant End (YYYY-MM-DD)',
                'size'     => 10,
                'disabled' => true
            )
        );
        $this->addElement(
            'text',
            'lastreport',
            array(
                'label'    => 'Last Report (YYYY-MM-DD)',
                'size'     => 10,
                'disabled' => true
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

                                                            //----------------------------------------------------------
                                                            // Section - CC recipient selection
                                                            //----------------------------------------------------------
        $this->addElement(
            'checkbox',
            'copyhospit',
            array(
                'label' => 'Kingdom Hospitaller'
            )
        );
        $this->addElement(
            'checkbox',
            'copychirurgeon',
            array(
                'label' => 'Kingdom Chirurgeon'
            )
        );
        $this->addElement(
            'text',
            'othercopy1',
            array(
                'label'      => 'Other Email',
                'validators' => array('emailAddress')
            )
        );
        $this->addElement(
            'text',
            'othercopy2',
            array(
                'label'      => 'Other Email',
                'validators' => array('emailAddress')
            )
        );
        $this->addDisplayGroup(
            array(
                'copyhospit',
                'copychirurgeon',
                'othercopy1',
                'othercopy2'
            ),
            'copies',
            array('legend' => 'Forward copies to:')
        );

                                                            //----------------------------------------------------------
                                                            // Section - report content
                                                            //----------------------------------------------------------
        $this->addElement(
            'textarea',
            'statistics',
            array(
                'label' => 'Statistics: Total and Active Members, Total Funds',
                'cols'  => 50,
                'rows'  => 2,
                'wrap'  => 'virtual'
            )
        );
        $this->addElement(
            'textarea',
            'activities',
            array(
                'label' => 'Summary of Regular Activities',
                'cols'  => 50,
                'rows'  => 10,
                'wrap'  => 'virtual'
            )
        );
        $this->addElement(
            'textarea',
            'achievements',
            array(
                'label' => 'Special Achievements and Ideas that Worked',
                'cols'  => 50,
                'rows'  => 10,
                'wrap'  => 'virtual'
            )
        );
        $this->addElement(
            'textarea',
            'events',
            array(
                'label' => 'Summary of Events',
                'cols'  => 50,
                'rows'  => 10,
                'wrap'  => 'virtual'
            )
        );
        $this->addElement(
            'textarea',
            'problems',
            array(
                'label' => 'Problems of Note',
                'cols'  => 50,
                'rows'  => 10,
                'wrap'  => 'virtual'
            )
        );
        $this->addElement(
            'textarea',
            'questions',
            array(
                'label' => 'Questions',
                'cols'  => 50,
                'rows'  => 10,
                'wrap'  => 'virtual'
            )
        );
        $this->addElement(
            'textarea',
            'plans',
            array(
                'label' => 'Plans for the Future, Ideas, etc',
                'cols'  => 50,
                'rows'  => 10,
                'wrap'  => 'virtual'
            )
        );
        $this->addElement(
            'textarea',
            'comments',
            array(
                'label' => 'General Comments',
                'cols'  => 50,
                'rows'  => 10,
                'wrap'  => 'virtual'
            )
        );
        $this->addDisplayGroup(
            array(
                'statistics',
                'activities',
                'achievements',
                'events',
                'problems',
                'questions',
                'plans',
                'comments'
            ),
            'report',
            array('legend' => 'Report Details')
        );

                                                            //----------------------------------------------------------
                                                            // Section - officer reports
                                                            //----------------------------------------------------------
        $this->addElement(
            'textarea',
            'summarshal',
            array(
                'label' => 'Marshal',
                'cols'  => 50,
                'rows'  => 10,
                'wrap'  => 'virtual'
            )
        );
        $this->addElement(
            'textarea',
            'sumherald',
            array(
                'label' => 'Herald',
                'cols'  => 50,
                'rows'  => 10,
                'wrap'  => 'virtual'
            )
        );
        $this->addElement(
            'textarea',
            'sumartssci',
            array(
                'label' => 'Arts and Sciences',
                'cols'  => 50,
                'rows'  => 10,
                'wrap'  => 'virtual'
            )
        );
        $this->addElement(
            'textarea',
            'sumreeve',
            array(
                'label' => 'Reeve',
                'cols'  => 50,
                'rows'  => 10,
                'wrap'  => 'virtual'
            )
        );
        $this->addElement(
            'textarea',
            'sumconstable',
            array(
                'label' => 'Constable',
                'cols'  => 50,
                'rows'  => 10,
                'wrap'  => 'virtual'
            )
        );
        $this->addElement(
            'textarea',
            'sumchirurgeon',
            array(
                'label' => 'Chirurgeon',
                'cols'  => 50,
                'rows'  => 10,
                'wrap'  => 'virtual'
            )
        );
        $this->addElement(
            'textarea',
            'sumchronicler',
            array(
                'label' => 'Chronicler and/or Webminister',
                'cols'  => 50,
                'rows'  => 10,
                'wrap'  => 'virtual'
            )
        );
        $this->addElement(
            'textarea',
            'sumchatelaine',
            array(
                'label' => 'Chatelaine/Hospitaller',
                'cols'  => 50,
                'rows'  => 10,
                'wrap'  => 'virtual'
            )
        );
        $this->addElement(
            'textarea',
            'sumlists',
            array(
                'label' => 'Lists',
                'cols'  => 50,
                'rows'  => 10,
                'wrap'  => 'virtual'
            )
        );
        $this->addDisplayGroup(
            array(
                'summarshal',
                'sumherald',
                'sumartssci',
                'sumreeve',
                'sumconstable',
                'sumchirurgeon',
                'sumchronicler',
                'sumchatelaine',
                'sumlists'
            ),
            'officers',
            array('legend' => 'Summary of Officer Reports')
        );

    }

}
