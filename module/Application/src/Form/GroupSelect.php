<?php

declare(strict_types=1);

namespace Application\Form;

use Laminas\Form\Form;

class GroupSelect extends Form
{
    public function __construct($groupOptions = [])
    {
        parent::__construct();

        $this->setAttribute('method', 'get');
        $this->setAttribute('class', 'form--auto-submit');

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
            'name'       => 'groupsubmit',
            'options'    => [],
            'attributes' => [
                'value' => 'Select',
            ],
        ]);
    }
}
