<?php

namespace App\Domain\Dto;

use App\Infrastructure\Eloquent\EventEloquent;
use App\Domain\Traits\FormatsDate;

class EventEditDto
{

    use FormatsDate;

    public int $id;
    public string $name;
    public string $eventDate; // 'Y-m-d H:i' フォーマット
    public string $eventEndDate; // 'Y-m-d H:i' フォーマット
    public ?string $memo;
    public ?string $impression;
    public ?string $googleEventId;
    public array $alertIntervals; // [ ['minute_before_event' => 10], ... ]
    public array $tagIds; // [1, 2, 3]

    public function __construct(EventEloquent $event) 
    {
        $this->id   = $event->id;
        $this->name = $event->name;
        $this->eventDate = $this->formatDate($event->event_date);
        $this->eventEndDate = $this->formatDate($event->event_end_date);
        $this->memo = $event->memo;
        $this->impression = $event->impression;
        $this->googleEventId = $event->google_event_id;
        $this->alertIntervals = $event->alertIntervals->map(
            fn($a) => ['minute_before_event' => $a->minute_before_event]
        )->toArray();
        $this->tagIds = $event->tags->pluck('id')->toArray();
    }
}
