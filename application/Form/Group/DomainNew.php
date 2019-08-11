<?php

namespace SenDb\Form\Group;

class DomainNew extends \Zend_Form
{
    public function init()
    {
        $this->setAction('#');
        $this->setDecorators(array('FormElements', 'Form'));
        $this->setElementDecorators(array('ViewHelper'));

        $this->addElement(
            'select',
            'groupidnew'
        );
        $this->addElement(
            'text',
            'domainnew',
            array(
                'required'   => true,
                'filters'    => array(
                    'stringTrim',
                    'stringToLower'
                ),
                'validators' => array('alnum')
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
