<?php

namespace App\Domain\Models\AlertInterval;

use App\Domain\Models\AlertInterval\AlertIntervalId;
use App\Domain\Models\Event\EventId;
use App\Domain\Models\AlertInterval\AlertIntervalMinuteBeforeEvent;

class AlertInterval
{
    private ?AlertIntervalId $id = null;
    private EventId $eventId;
    private AlertIntervalMinuteBeforeEvent $minuteBeforeEvent;

    public function __construct(
        EventId $eventId,
        AlertIntervalMinuteBeforeEvent $minuteBeforeEvent,
        ?AlertIntervalId $id = null
    ) {
        $this->id = $id;
        $this->eventId = $eventId;
        $this->minuteBeforeEvent = $minuteBeforeEvent;
    }

    public function getId(): AlertIntervalId
    {
        return $this->id;
    }

    public function getEventId(): EventId
    {
        return $this->eventId;
    }

    public function getAlertIntervalMinuteBeforeEvent(): AlertIntervalMinuteBeforeEvent
    {
        return $this->minuteBeforeEvent;
    }
}
