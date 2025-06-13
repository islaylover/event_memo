<?php

namespace App\Domain\Models\Event;

use App\Domain\Utility\Validator\DateValidator;
use DateTime;
use Exception;
use Carbon\Carbon;

class EventDate
{
    private DateTime $eventDate;

    public function __construct($eventDate)
    {
        // バリデーションとパース
        $this->eventDate = DateValidator::validateDatetime($eventDate, 'イベント開始日時');
    }

    public function getValue(): DateTime
    {
        return $this->eventDate;
    }

    /**
     * Carbonインスタンスを返す
     */
    public function toCarbon(): Carbon
    {
        return Carbon::instance($this->eventDate);
    }

}
