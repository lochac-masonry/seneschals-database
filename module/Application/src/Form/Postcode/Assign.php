<?php

namespace Application\Form\Postcode;

use Zend\Form\{Fieldset, Form};
use Zend\InputFilter\InputFilterProviderInterface;

class Assign extends Form
{
    public function __construct($groupOptions = [])
    {
        parent::__construct();

        $this->setAttribute('class', 'form--block');

        $this->add(new class($groupOptions) extends Fieldset implements InputFilterProviderInterface {
            public function __construct($groupOptions = [])
            {
                parent::__construct('assign', []);

                $this->add([
                    'type'    => 'number',
                    'name'    => 'rangestart',
                    'options' => [
                        'label' => 'Postcode Range Start:',
                    ],
                    'attributes' => [
                        'required' => true,
                        'step'     => 1,
                        'min'      => 1,
                        'max'      => 9999,
                    ],
                ]);
                $this->add([
                    'type'    => 'number',
                    'name'    => 'rangeend',
                    'options' => [
                        'label' => 'Range End:',
                    ],
                    'attributes' => [
                        'required' => true,
                        'step'     => 1,
                        'min'      => 1,
                        'max'      => 9999,
                    ],
                ]);
                $this->add([
                    'type'    => 'select',
                    'name'    => 'group',
                    'options' => [
                        'label'         => 'Assign to:',
                        'value_options' => $groupOptions,
                    ],
                    'attributes' => [
                        'required' => true,
                    ],
                ]);
                $this->add([
                    'type'    => 'csrf',
                    'name'    => 'csrf',
                    'options' => [
                        'csrf_options' => ['timeout' => 60 * 30],
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
