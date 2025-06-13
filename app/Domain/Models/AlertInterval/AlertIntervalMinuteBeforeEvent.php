<?php

namespace App\Domain\Models\AlertInterval;

use App\Domain\Utility\Validator\NumberValidator;

class AlertIntervalMinuteBeforeEvent
{
    private int $minuteBeforeEvent;

    public function __construct($minuteBeforeEvent)
    {
        $this->minuteBeforeEvent = NumberValidator::validateNumber($minuteBeforeEvent, [
            'label' => '告知メール間隔分',
        ]);
    }

    public function getValue(): int
    {
        return $this->minuteBeforeEvent;
    }
}
