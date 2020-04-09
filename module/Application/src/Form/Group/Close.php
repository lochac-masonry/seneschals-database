<?php

declare(strict_types=1);

namespace Application\Form\Group;

use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;

class Close extends Form
{
    public function __construct($groupOptions = [])
    {
        parent::__construct();

        $this->setAttribute('class', 'form--block');

        $this->add(
            new class ($groupOptions) extends Fieldset implements InputFilterProviderInterface
            {
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
            }
        );

        $this->add([
            'type'    => 'csrf',
            'name'    => 'csrf',
            'options' => [
                'csrf_options' => ['timeout' => 60 * 30],
            ],
            'attributes' => [],
        ]);
    }
}
