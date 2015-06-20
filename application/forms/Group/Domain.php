<?php

class SenDb_Form_Group_Domain extends Zend_Form
{
    public function init()
    {
        $id = $this->getAttrib('suffix');

        $this->setAction('#');
        $this->setDecorators(array('FormElements', 'Form'));
        $this->setElementDecorators(array('ViewHelper'));

        $this->addElement(
            'select',
            'groupid'.$id
        );
        $this->addElement(
            'text',
            'domain'.$id,
            array(
                'required'   => true,
                'filters'    => array(
                    'stringTrim',
                    'stringToLower'
                ),
                'validators' => array('alpha')
            )
        );
        $this->addElement(
            'submit',
            'submit'.$id,
            array(
                'label' => 'Save'
            )
        );
        $this->addElement(
            'submit',
            'delete'.$id,
            array(
                'label' => 'Delete'
            )
        );

    }

}
