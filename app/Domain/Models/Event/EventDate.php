<?php

namespace App\Domain\Models\Event;

use App\Domain\Utility\Validator\DateValidator;
use DateTime;
use Exception;

class EventDate
{
    private DateTime $event_date;

    public function __construct($event_date)
    {
        // バリデーションとパース
        $this->event_date = DateValidator::validateDatetime($event_date, 'イベント年月日');
    }

    public function getValue(): DateTime
    {
        return $this->event_date;
    }

}
