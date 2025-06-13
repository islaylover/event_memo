<?php

namespace App\Domain\Models\Event;

use App\Domain\Models\Event\EventId;
use App\Domain\Models\Event\EventUserId;
use App\Domain\Models\Event\EventName;
use App\Domain\Models\Event\EventDate;
use App\Domain\Models\Event\EventEndDate;
use App\Domain\Models\Event\EventMemo;
use App\Domain\Models\Event\EventImpression;
use App\Domain\Models\Event\GoogleEventId;

class Event
{
    private ?EventId $id = null;
    private EventUserId $userId;
    private EventName $name;
    private EventDate $eventDate;
    private EventEndDate $eventEndDate;
    private EventMemo $memo;
    private EventImpression $impression;
    private GoogleEventId $googleEventId;

    public function __construct(
        EventUserId $userId,
        EventName $name,
        EventDate $eventDate,
        EventEndDate $eventEndDate,
        EventMemo $memo,
        EventImpression $impression,
        GoogleEventId $googleEventId = null,
        ?EventId $id = null
    ) {
        $this->id      = $id;
        $this->userId = $userId;
        $this->name    = $name;
        $this->eventDate = $eventDate;
        $this->eventEndDate = $eventEndDate;
        $this->memo = $memo;
        $this->impression = $impression;
        $this->googleEventId = $googleEventId;
    }

    public function setId(EventId $id): void
    {
        $this->id = $id;
    }

    public function getId(): EventId
    {
        return $this->id;
    }

    public function getUserId(): EventUserId
    {
        return $this->userId;
    }

    public function getEventName(): EventName
    {
        return $this->name;
    }

    public function getEventDate(): EventDate
    {
        return $this->eventDate;
    }

    public function getEventEndDate(): EventEndDate
    {
        return $this->eventEndDate;
    }

    public function getEventMemo(): EventMemo
    {
        return $this->memo;
    }

    public function getEventImpression(): EventImpression
    {
        return $this->impression;
    }

    public function getGoogleEventId(): GoogleEventId
    {
        return $this->googleEventId;
    }

}
