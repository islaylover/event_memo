<?php

namespace App\Domain\Traits;

use DateTime;
use DateTimeInterface;
use Exception;

trait FormatsDate
{
    protected function formatDate($value, string $dateFormat = 'Y-m-d H:i'): string
    {
        if (empty($value)) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format($dateFormat);
        }

        try {
            return (new \DateTime($value))->format($dateFormat);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Invalid date format: {$value}");
        }
    }
}
