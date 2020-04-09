<?php

namespace Application\Form\Group;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

class Domain extends Form implements InputFilterProviderInterface
{
    public function __construct($id, $existing, $groupOptions = [])
    {
        parent::__construct();

        $this->formId = $id;

        $this->add([
            'type'    => 'select',
            'name'    => 'groupid' . $id,
            'options' => [
                'empty_option'  => 'Please select one...',
                'value_options' => $groupOptions,
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);
        $this->add([
            'type'       => 'text',
            'name'       => 'domain' . $id,
            'options'    => [],
            'attributes' => [
                'size'     => 25,
                'title'    => 'Enter lowercase letters and numbers only.',
                'pattern'  => '[a-z0-9]+',
                'required' => true,
            ],
        ]);

        $this->add([
            'type'    => 'csrf',
            'name'    => 'csrf' . $id,
            'options' => [
                'csrf_options' => ['timeout' => 60 * 30],
            ],
            'attributes' => [],
        ]);

        // Add action buttons - update/delete for existing records, create for new.
        if ($existing) {
            $this->add([
                'type'       => 'submit',
                'name'       => 'update' . $id,
                'options'    => [],
                'attributes' => [
                    'value' => 'Save',
                ],
            ]);
            $this->add([
                'type'       => 'submit',
                'name'       => 'delete' . $id,
                'options'    => [],
                'attributes' => [
                    'value' => 'Delete',
                ],
            ]);
        } else {
            $this->add([
                'type'       => 'submit',
                'name'       => 'create' . $id,
                'options'    => [],
                'attributes' => [
                    'value' => 'Add New',
                ],
            ]);
        }
    }

    public function getInputFilterSpecification()
    {
        return [
            'domain' . $this->formId => [
                'required'   => true,
                'validators' => [
                    [
                        'name'    => 'regex',
                        'options' => ['pattern' => '/^[a-z0-9]+$/'],
                    ],
                ],
            ],
        ];
    }
}
