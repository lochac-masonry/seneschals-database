<?php

class SenDb_Form_PostCode_Assign extends Zend_Form
{
    public function init()
    {
        $this->setAction('#');

        $this->addElement(
            'text',
            'rangestart',
            array(
                'label'      => 'Postcode Range Start:',
                'required'   => true,
                'maxlength'  => 4,
                'filters'    => array('stringTrim'),
                'validators' => array(
                    'int',
                    array(
                        'stringLength',
                        false,
                        array(0,4)
                    )
                )
            )
        );
        $this->addElement(
            'text',
            'rangeend',
            array(
                'label'      => 'Range End:',
                'required'   => true,
                'maxlength'  => 4,
                'filters'    => array('stringTrim'),
                'validators' => array(
                    'int',
                    array(
                        'stringLength',
                        false,
                        array(0,4)
                    )
                )
            )
        );
        $this->addElement(
            'select',
            'group',
            array(
                'label' => 'Assign to:'
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
                'rangestart',
                'rangeend',
                'group',
                'submit'
            ),
            'assign'
        );

    }

}
