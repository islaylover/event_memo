<?php

namespace App\Domain\Dto;

class EventEditDto
{
    public int $id;
    public string $name;
    public string $event_date; // 'Y-m-d H:i' フォーマット
    public string $impression;
    public array $alert_intervals; // [ ['minute_before_event' => 10], ... ]
    public array $tag_ids; // [1, 2, 3]

    public function __construct(
        int $id,
        string $name,
        string $event_date,
        string $impression,
        array $alert_intervals,
        array $tag_ids
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->event_date = $event_date;
        $this->impression = $impression;
        $this->alert_intervals = $alert_intervals;
        $this->tag_ids = $tag_ids;
    }
}
