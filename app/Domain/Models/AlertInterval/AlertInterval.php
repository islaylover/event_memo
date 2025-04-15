<?php

namespace App\Domain\Models\AlertInterval;

use App\Domain\Models\AlertInterval\AlertIntervalId;
use App\Domain\Models\Event\EventId;
use App\Domain\Models\AlertInterval\AlertIntervalMinuteBeforeEvent;

class AlertInterval
{
    private ?AlertIntervalId $id = null;
    private EventId $event_id;
    private AlertIntervalMinuteBeforeEvent $minute_before_event;

    public function __construct(
        EventId $event_id,
        AlertIntervalMinuteBeforeEvent $minute_before_event,
        ?AlertIntervalId $id = null
    ) {
        $this->id = $id;
        $this->event_id = $event_id;
        $this->minute_before_event = $minute_before_event;
    }

    public function getId(): AlertIntervalId
    {
        return $this->id;
    }

    public function getEventId(): EventId
    {
        return $this->event_id;
    }

    public function getAlertIntervalMinuteBeforeEvent(): AlertIntervalMinuteBeforeEvent
    {
        return $this->minute_before_event;
    }
}
