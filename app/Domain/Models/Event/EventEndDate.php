<?php

namespace App\Domain\Models\Event;

use App\Domain\Utility\Validator\DateValidator;
use DateTime;
use Exception;
use Carbon\Carbon;

class EventEndDate
{
    private ?DateTime $eventEndDate;

    public function __construct($eventEndDate)
    {
        // バリデーションとパース
        $this->eventEndDate = empty($eventEndDate)
            ? null
            :DateValidator::validateDatetime($eventEndDate, 'イベント開始日時');
    }

    public function getValue(): ?DateTime
    {
        return $this->eventEndDate;
    }

    /**
     * Carbonインスタンスを返す
     */
    public function toCarbon(): Carbon
    {
        return Carbon::instance($this->eventEndDate);
    }

}
