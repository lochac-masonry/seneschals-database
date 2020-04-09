<?php

declare(strict_types=1);

namespace Application\Form\Event;

use Laminas\Form\Form;

class DeleteAttachment extends Form
{
    public function __construct($redirectUrl)
    {
        parent::__construct();

        $this->add([
            'type'       => 'hidden',
            'name'       => 'redirectUrl',
            'options'    => [],
            'attributes' => [
                'value' => $redirectUrl,
            ],
        ]);
        $this->add([
            'type'       => 'submit',
            'name'       => 'submit',
            'options'    => [],
            'attributes' => [
                'value' => 'Confirm',
            ],
        ]);
        $this->add([
            'type'       => 'submit',
            'name'       => 'back',
            'options'    => [],
            'attributes' => [
                'value' => 'Cancel and Go Back',
            ],
        ]);
    }
}
