<?php

namespace App\Domain\Models\AlertInterval;

use App\Domain\Utility\Validator\NumberValidator;

class AlertIntervalMinuteBeforeEvent
{
    private int $minute_before_event;

    public function __construct($minute_before_event)
    {
        $this->minute_before_event = NumberValidator::validateNumber($minute_before_event, [
            'label' => '告知メール間隔分',
        ]);
    }

    public function getValue(): int
    {
        return $this->minute_before_event;
    }
}
