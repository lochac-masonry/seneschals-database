<?php

namespace SenDb\Form\Group;

class AliasNew extends \Zend_Form
{
    public function init()
    {
        $this->setAction('#');
        $this->setDecorators(array('FormElements', 'Form'));
        $this->setElementDecorators(array('ViewHelper'));

        $this->addElement(
            'text',
            'aliasnew',
            array(
                'required'   => true,
                'size'       => 25,
                'filters'    => array('stringTrim'),
                'validators' => array('emailAddress')
            )
        );
        $this->addElement(
            'text',
            'addressnew',
            array(
                'required'   => true,
                'size'       => 25,
                'filters'    => array('stringTrim'),
                'validators' => array('emailAddress')
            )
        );
        $this->addElement(
            'submit',
            'submitnew',
            array(
                'label' => 'Add New'
            )
        );
    }
}
