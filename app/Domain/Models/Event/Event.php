<?php

namespace App\Domain\Models\Event;

use App\Domain\Models\Event\EventId;
use App\Domain\Models\Event\EventUserId;
use App\Domain\Models\Event\EventName;
use App\Domain\Models\Event\EventDate;
use App\Domain\Models\Event\EventImpression;

class Event
{
    private ?EventId $id = null;
    private EventUserId $user_id;
    private EventName $name;
    private EventDate $event_date;
    private EventImpression $impression;

    public function __construct(
        EventUserId $user_id,
        EventName $name,
        EventDate $event_date, 
        EventImpression $impression,
        ?EventId $id = null
    ) {
        $this->id      = $id;
        $this->user_id = $user_id;
        $this->name    = $name;
        $this->event_date = $event_date;
        $this->impression = $impression;
    }

    public function getId(): EventId
    {
        return $this->id;
    }

    public function getUserId(): EventUserId
    {
        return $this->user_id;
    }

    public function getEventName(): EventName
    {
        return $this->name;
    }

    public function getEventDate(): EventDate
    {
        return $this->event_date;
    }

    public function getEventImpression(): EventImpression
    {
        return $this->impression;
    }

}
