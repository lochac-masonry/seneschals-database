<?php

class SenDb_Form_PostCode_Query extends Zend_Form
{
    public function init()
    {
        $this->setAction('#');

        $this->addElement(
            'checkbox',
            'printable',
            array(
                'label' => 'Printable Report?'
            )
        );

                                                            //----------------------------------------------------------
                                                            // Section - query by group
                                                            //----------------------------------------------------------
        $this->addElement(
            'select',
            'group',
            array(
                'label' => 'Group name:'
            )
        );
        $this->addElement(
            'submit',
            'querybygroup',
            array(
                'label' => 'Submit'
            )
        );
        $this->addDisplayGroup(
            array(
                'group',
                'querybygroup'
            ),
            'byGroup',
            array('legend' => 'Search by Group')
        );

                                                            //----------------------------------------------------------
                                                            // Section - query by postcode
                                                            //----------------------------------------------------------
        $this->addElement(
            'text',
            'postcode',
            array(
                'label'      => 'Postcode:',
                'maxlength'  => 4,
                'validators' => array(
                    array(
                        'stringLength',
                        false,
                        array(0, 4)
                    )
                )
            )
        );
        $this->addElement(
            'submit',
            'querybycode',
            array(
                'label' => 'Submit'
            )
        );
        $this->addDisplayGroup(
            array(
                'postcode',
                'querybycode'
            ),
            'byCode',
            array('legend' => 'Search by Postcode')
        );

                                                            //----------------------------------------------------------
                                                            // Section - query by postcode range
                                                            //----------------------------------------------------------
        $this->addElement(
            'text',
            'rangestart',
            array(
                'label'      => 'Range Start:',
                'maxlength'  => 4,
                'validators' => array(
                    array(
                        'stringLength',
                        false,
                        array(0, 4)
                    )
                )
            )
        );
        $this->addElement(
            'text',
            'rangeend',
            array(
                'label'      => 'Range End:',
                'maxlength'  => 4,
                'validators' => array(
                    array(
                        'stringLength',
                        false,
                        array(0, 4)
                    )
                )
            )
        );
        $this->addElement(
            'submit',
            'querybyrange',
            array(
                'label' => 'Submit'
            )
        );
        $this->addDisplayGroup(
            array(
                'rangestart',
                'rangeend',
                'querybyrange'
            ),
            'byRange',
            array('legend' => 'Search by Postcode Range')
        );

                                                            //----------------------------------------------------------
                                                            // Section - query by locality
                                                            //----------------------------------------------------------
        $this->addElement(
            'text',
            'locality',
            array(
                'label'      => 'Suburb/Locality Name:',
                'maxlength'  => 64,
                'validators' => array(
                    array(
                        'stringLength',
                        false,
                        array(0,64)
                    )
                )
            )
        );
        $this->addElement(
            'submit',
            'querybylocality',
            array(
                'label' => 'Submit'
            )
        );
        $this->addDisplayGroup(
            array(
                'locality',
                'querybylocality'
            ),
            'byLocality',
            array('legend' => 'Search by Suburb Name')
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
