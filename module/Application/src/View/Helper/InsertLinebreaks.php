<?php

declare(strict_types=1);

namespace Application\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class InsertLinebreaks extends AbstractHelper
{
    public function __invoke(string $text)
    {
        return \str_replace("\n", '<br>', $text);
    }
}
