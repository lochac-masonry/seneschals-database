<?php

namespace SenDb\Form\Event;

class ListFilter extends \Zend_Form
{
    public function init()
    {
        $this->setAction('#');
        $this->setDecorators(array(
            'FormElements',
            'Form'
        ));
        $this->setElementDecorators(array(
            'ViewHelper',
            'Label'
        ));

        $this->addElement(
            'select',
            'groupid',
            array(
                'label' => 'Select group:'
            )
        );
        $this->addElement(
            'select',
            'status',
            array(
                'label'        => 'Status:',
                'multiOptions' => array(
                    'new'      => 'New',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected'
                )
            )
        );
        $this->addElement(
            'select',
            'tense',
            array(
                'label'        => 'Tense:',
                'multiOptions' => array(
                    'future' => 'Future',
                    'past'   => 'Past',
                    'both'   => 'Both'
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
