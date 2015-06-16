<?php

class SenDb_Form_Report_GroupSelect extends Zend_Form
{
    public init()
    {
        $this->setAction('#');
        $this->setMethod('get');
        $this->setDecorators(array('FormElements', 'Form'));

        $this->addElement(
            'select',
            'groupid',
            array(
                'label'      => 'Select group:',
                'validators' => array('digits'),
                'required'   => true,
                'decorators' => array(
                    'ViewHelper',
                    'Label'
                )
            )
        );
        $this->addElement(
            'submit',
            'submit',
            array(
                'label'      => 'Select',
                'decorators' => array('ViewHelper')
            )
        );

    }

}
