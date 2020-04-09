<?php

namespace Application\Form\Event;

use Laminas\Form\Fieldset;

class AttachmentFieldset extends Fieldset
{
    private $attachment;

    public function __construct($attachment)
    {
        parent::__construct('attachment' . $attachment['id'], []);
        $this->attachment = $attachment;

        $this->setLabel(sprintf('%s (%s)', $attachment['name'], $this->getFileSizeString()));

        $this->add([
            'type'       => 'button',
            'name'       => 'download',
            'options'    => [
                'label' => 'Download',
            ],
            'attributes' => [
                'data-link' => $attachment['downloadLink'],
            ],
        ]);

        $this->add([
            'type'       => 'button',
            'name'       => 'delete',
            'options'    => [
                'label' => 'Delete',
            ],
            'attributes' => [
                'data-link' => $attachment['deleteLink'],
            ],
        ]);
    }

    private function getFileSizeString()
    {
        $bytes = $this->attachment['size'];
        $suffixes = ['B', 'kB', 'MB'];
        for ($i = 0; $bytes >= 1024 && $i < count($suffixes); $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 1) . $suffixes[$i];
    }
}
