<?php

namespace Application\Form\Group;

use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

class Alias extends Form implements InputFilterProviderInterface
{
    public function __construct($id, $aliasRegex, $existing)
    {
        parent::__construct();

        $this->formId = $id;
        $this->aliasRegex = $aliasRegex;

        $this->add([
            'type'       => 'email',
            'name'       => 'alias' . $id,
            'options'    => [],
            'attributes' => [
                'size'     => 25,
                'title'    => 'Enter an email address ending in one of your configured domains.',
                'pattern'  => $aliasRegex,
                'required' => true,
            ],
        ]);
        $this->add([
            'type'       => 'email',
            'name'       => 'address' . $id,
            'options'    => [],
            'attributes' => [
                'size'     => 25,
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
        $aliasSpec = $this->get('alias' . $this->formId)->getInputSpecification();
        $aliasSpec['validators'][] = [
            'name'    => 'regex',
            'options' => ['pattern' => '/^' . $this->aliasRegex . '$/'],
        ];
        return [
            'alias' . $this->formId => $aliasSpec,
        ];
    }
}
