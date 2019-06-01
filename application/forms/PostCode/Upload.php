<?php

class SenDb_Form_PostCode_Upload extends Zend_Form
{
    public function init()
    {
        $this->setAction('#');

        $this->addElement(
            'file',
            'userfile',
            array(
                'required'   => true,
                'validators' => array(
                    array(
                        'Size',
                        false,
                        2560000
                    ),
                    array(
                        'Extension',
                        false,
                        'csv'
                    )
                )
            )
        );
        $this->addElement(
            'submit',
            'submit',
            array(
                'label' => 'Submit'
            )
        );
    }
}
