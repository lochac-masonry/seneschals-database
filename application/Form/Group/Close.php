<?php

namespace SenDb\Form\Group;

class Close extends \Zend_Form
{
    public function init()
    {
        $this->setAction('#');

        $this->addElement(
            'select',
            'group_close',
            array(
                'label' => 'Close group:'
            )
        );
        $this->addElement(
            'select',
            'group_get',
            array(
                'label' => 'Give postcodes to:'
            )
        );
        $this->addElement(
            'checkbox',
            'confirm',
            array(
                'label' => 'Confirm:'
            )
        );
        $this->addElement(
            'submit',
            'submit',
            array(
                'label'    => 'Submit',
                'required' => true
            )
        );
        $this->addDisplayGroup(
            array(
                'group_close',
                'group_get',
                'confirm',
                'submit'
            ),
            'close'
        );
    }
}
