<?php

namespace App\Domain\Dto;

use App\Infrastructure\Eloquent\EventEloquent;
use App\Domain\Traits\FormatsDate;

class EventSummaryDto
{

    use FormatsDate;

    public int $id;
    public string $name;
    public string $eventDate;
    public string $eventEndDate;
    public ?string $memo;
    public ?string $impression;
    public ?string $googleEventId;
    public array $alertIntervals;
    public array $tags;

    public function __construct(EventEloquent $event) {
        $this->id = $event->id;
        $this->name = $event->name;
        $this->eventDate = $this->formatDate($event->event_date);
        $this->eventEndDate = $this->formatDate($event->event_end_date);
        $this->memo = $event->memo;
        $this->impression = $event->impression;
        $this->googleEventId = $event->google_event_id;
        $this->alertIntervals = $event->alertIntervals->pluck('minute_before_event')->toArray();
        $this->tags = $event->tags->pluck('name')->toArray();
    }
}
