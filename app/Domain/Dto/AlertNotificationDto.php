<?php

namespace App\Domain\Dto;

class AlertNotificationDto
{
    public int $event_id;
    public string $event_name;
    public string $event_date;
    public string $user_name;
    public string $user_email;
    public int $minute_before_event;
    public array $tags;

    public function __construct(
        int $event_id,
        string $event_name,
        string $event_date,
        string $user_name,
        string $user_email,
        int $minute_before_event,
        array $tags = []
    ) {
        $this->event_id = $event_id;
        $this->event_name = $event_name;
        $this->event_date = $event_date;
        $this->user_name = $user_name;
        $this->user_email = $user_email;
        $this->minute_before_event = $minute_before_event;
        $this->tags = $tags;
    }
}
