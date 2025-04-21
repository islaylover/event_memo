<?php

namespace App\Domain\Dto;

class EventSummaryDto
{
    public int $id;
    public string $name;
    public string $eventDate;
    public string $impression;
    public array $alertIntervals;
    public array $tags;

    public function __construct(
        int $id,
        string $name,
        string $eventDate,
        string $impression,
        array $alertIntervals, // int[]（分前）
        array $tags // string[]（タグ名）
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->eventDate = $eventDate;
        $this->impression = $impression;
        $this->alertIntervals = $alertIntervals;
        $this->tags = $tags;
    }
}
