<?php

namespace App\Domain\Dto;

use App\Domain\Traits\FormatsDate;

class AlertNotificationDto
{

    use FormatsDate;

    public int $eventId;
    public string $eventName;
    public string $eventDate;
    public ?string $eventEndDate;
    public ?string $memo;
    public string $userName;
    public string $userEmail;
    public int $minuteBeforeEvent;
    public array $tags;

    public function __construct(
        int $eventId,
        string $eventName,
        string $eventDate,
        ?string $eventEndDate,
        ?string $memo,
        string $userName,
        string $userEmail,
        int $minuteBeforeEvent,
        array $tags = []
    ) {
        $this->eventId = $eventId;
        $this->eventName = $eventName;
        $this->eventDate = $this->formatDate($eventDate);
        $this->eventEndDate = $this->formatDate($eventEndDate);
        $this->memo = $memo;
        $this->userName = $userName;
        $this->userEmail = $userEmail;
        $this->minuteBeforeEvent = $minuteBeforeEvent;
        $this->tags = $tags;
    }
}
