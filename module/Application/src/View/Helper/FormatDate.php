<?php

declare(strict_types=1);

namespace Application\View\Helper;

use Laminas\View\Helper\AbstractHelper;

class FormatDate extends AbstractHelper
{
    public function __invoke(string $dateStart, string $dateEnd = null)
    {
        if ($dateEnd === null) {
            $dateEnd = $dateStart;
        }

        $datetimeStart = new \DateTimeImmutable($dateStart);
        $datetimeEnd = new \DateTimeImmutable($dateEnd);
        $dateStartParts = \date_parse($dateStart);
        $dateEndParts = \date_parse($dateEnd);
        $nowParts = \getdate();
        $yearIfDifferent = $nowParts['year'] === $dateStartParts['year'] ? '' : ' ' . $datetimeStart->format('Y');

        if ($dateStartParts['year'] !== $dateEndParts['year']) {
            return $datetimeStart->format('j F Y') . ' - ' . $datetimeEnd->format('j F Y');
        }
        if ($dateStartParts['month'] !== $dateEndParts['month']) {
            return $datetimeStart->format('j F') . ' - ' . $datetimeEnd->format('j F') . $yearIfDifferent;
        }
        if ($dateStartParts['day'] !== $dateEndParts['day']) {
            return $datetimeStart->format('j') . '-' . $datetimeEnd->format('j F') . $yearIfDifferent;
        }
        return $datetimeStart->format('j F') . $yearIfDifferent;
    }
}
