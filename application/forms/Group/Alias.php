<?php

class SenDb_Form_Group_Alias extends Zend_Form
{
    public function init()
    {
        $id = $this->getAttrib('suffix');

        $this->setAction('#');
        $this->setDecorators(array('FormElements', 'Form'));
        $this->setElementDecorators(array('ViewHelper'));

        $this->addElement(
            'text',
            'alias' . $id,
            array(
                'required'   => true,
                'size'       => 25,
                'filters'    => array('stringTrim'),
                'validators' => array('emailAddress')
            )
        );
        $this->addElement(
            'text',
            'address' . $id,
            array(
                'required'   => true,
                'size'       => 25,
                'filters'    => array('stringTrim'),
                'validators' => array('emailAddress')
            )
        );
        $this->addElement(
            'submit',
            'submit' . $id,
            array(
                'label' => 'Save'
            )
        );
        $this->addElement(
            'submit',
            'delete' . $id,
            array(
                'label' => 'Delete'
            )
        );
    }
}
