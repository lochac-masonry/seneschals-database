<?php

namespace Application\Form;

use Zend\Form\Form;

class GroupSelect extends Form
{
    public function __construct($groupOptions = [])
    {
        parent::__construct();

        $this->setAttribute('method', 'get');

        $this->add([
            'type'    => 'select',
            'name'    => 'groupid',
            'options' => [
                'label'         => 'Select group:',
                'empty_option'  => 'Please select one...',
                'value_options' => $groupOptions,
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);
        $this->add([
            'type'       => 'submit',
            'name'       => 'submit',
            'options'    => [],
            'attributes' => [
                'value' => 'Select',
            ],
        ]);
    }
}
