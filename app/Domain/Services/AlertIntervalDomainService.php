<?php

namespace App\Domain\Services;

class AlertIntervalDomainService
{
    public static function removeDuplicates(array $rawIntervals): array
    {
        return collect($rawIntervals)
            ->unique('minute_before_event')
            ->values()
            ->toArray();
    }
}
