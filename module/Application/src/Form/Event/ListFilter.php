<?php

namespace Application\Form\Event;

use Zend\Form\Form;

class ListFilter extends Form
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
            'type'    => 'select',
            'name'    => 'status',
            'options' => [
                'label'         => 'Status:',
                'value_options' => [
                    'new'      => 'New',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    'all'      => 'All',
                ],
            ],
            'attributes' => [],
        ]);
        $this->add([
            'type'    => 'select',
            'name'    => 'tense',
            'options' => [
                'label'         => 'Tense:',
                'value_options' => [
                    'future' => 'Future',
                    'past'   => 'Past',
                    'both'   => 'Both',
                ],
            ],
            'attributes' => [],
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
