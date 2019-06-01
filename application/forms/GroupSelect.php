<?php

class SenDb_Form_GroupSelect extends Zend_Form
{
    public function init()
    {
        $this->setAction('#');
        $this->setDecorators(array('FormElements', 'Form'));

        $this->addElement(
            'select',
            'groupid',
            array(
                'label'      => 'Select group:',
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
