<?php

namespace Application\Form\Postcode;

use Zend\Form\{Fieldset, Form};
use Zend\InputFilter\InputFilterProviderInterface;

class Upload extends Form
{
    public function __construct()
    {
        parent::__construct();

        $this->setAttribute('class', 'form--block');

        $this->add(new class extends Fieldset implements InputFilterProviderInterface {
            public function __construct()
            {
                parent::__construct('upload', []);

                $this->add([
                    'type'       => 'file',
                    'name'       => 'userfile',
                    'options'    => [],
                    'attributes' => [
                        'required' => true,
                        'accept'   => '.csv',
                    ],
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
                $fileSpec = $this->get('userfile')->getInputSpecification();
                $fileSpec['required'] = true;
                $fileSpec['validators'] = [
                    ['name' => 'fileSize', 'options' => ['max' => '2.5MB']],
                    ['name' => 'fileExtension', 'options' => ['extension' => 'csv']],
                ];
                return [
                    'userfile' => $fileSpec,
                ];
            }
        });
    }
}
