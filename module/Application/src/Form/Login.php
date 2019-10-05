<?php

namespace Application\Form;

use Zend\Form\Form;
use Zend\InputFilter\InputFilterProviderInterface;

class Login extends Form implements InputFilterProviderInterface
{
    public function __construct($redirectUrl)
    {
        parent::__construct();

        $this->setAttribute('class', 'form--block');

        $this->add([
            'type'    => 'text',
            'name'    => 'username',
            'options' => [
                'label' => 'Username',
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);
        $this->add([
            'type'    => 'password',
            'name'    => 'password',
            'options' => [
                'label' => 'Password',
            ],
            'attributes' => [
                'required' => true,
            ],
        ]);
        $this->add([
            'type'       => 'hidden',
            'name'       => 'redirectUrl',
            'options'    => [],
            'attributes' => [
                'value' => $redirectUrl,
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
                'value' => 'Sign In',
            ],
        ]);
    }

    public function getInputFilterSpecification()
    {
        return [
            'username' => [
                'required' => true,
            ],
            'password' => [
                'required' => true,
            ],
        ];
    }
}
