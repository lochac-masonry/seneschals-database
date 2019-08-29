<?php

namespace Application\Form\Group;

use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class Close extends Form
{
    public function __construct($groupOptions = [])
    {
        parent::__construct();

        $this->setAttribute('class', 'form--block');

        $this->add(new class($groupOptions) extends Fieldset implements InputFilterProviderInterface {
            public function __construct($groupOptions = [])
            {
                parent::__construct('close', []);

                $this->add([
                    'type'    => 'select',
                    'name'    => 'group_close',
                    'options' => [
                        'label'         => 'Close group:',
                        'empty_option'  => 'Please select one...',
                        'value_options' => $groupOptions,
                    ],
                    'attributes' => [
                        'required' => true,
                    ],
                ]);
                $this->add([
                    'type'    => 'select',
                    'name'    => 'group_get',
                    'options' => [
                        'label'         => 'Give postcodes to:',
                        'empty_option'  => 'Please select one...',
                        'value_options' => $groupOptions,
                    ],
                    'attributes' => [
                        'required' => true,
                    ],
                ]);
                $this->add([
                    'type'    => 'checkbox',
                    'name'    => 'confirm',
                    'options' => [
                        'label' => 'Confirm:',
                    ],
                    'attributes' => [],
                ]);
                $this->add([
                    'type'       => 'submit',
                    'name'       => 'submit',
                    'options'    => [],
                    'attributes' => [
                        'value' => 'Submit',
                    ],
                ]);
            }

            public function getInputFilterSpecification()
            {
                return [];
            }
        });
    }
}
