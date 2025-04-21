<?php 

namespace App\Infrastructure\Repositories;

use App\Domain\Models\Event\Event;
use App\Domain\Models\Event\EventId;
use App\Domain\Models\Event\EventUserId;
use App\Domain\Models\Event\EventName;
use App\Domain\Models\Event\EventDate;
use App\Domain\Models\Event\EventImpression;
use App\Domain\Dto\EventSummaryDto;
use App\Domain\Dto\EventEditDto;
use App\Domain\Repositories\EventRepositoryInterface;
use App\Infrastructure\Eloquent\EventEloquent;

class EloquentEventRepository implements EventRepositoryInterface
{

    public function getAll(): array
    {
        return EventEloquent::all()->map(function ($eloquentEvent) {
            return new Event(
                new EventUserId($eloquentEvent->user_id),
                new EventName($eloquentEvent->name),
                new EventDate($eloquentEvent->event_date),
                new EventImpression($eloquentEvent->impression),
                new EventId($eloquentEvent->id)
            );
        })->all();//all() : convert result(collection) to array
    }

    public function getAllEventSummaries(EventUserId $eventUserId): array
    {
        $userId = $eventUserId->getValue();
        $events = EventEloquent::with(['tags', 'alertIntervals'])
            ->where('user_id', $userId)
            ->whereHas('tags', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->orderBy('event_date', 'asc')
            ->get();
        return $events->map(function ($event) {
            return new EventSummaryDto(
                $event->id,
                $event->name,
                $event->event_date->format('Y-m-d H:i'),
                $event->impression ?? '',
                $event->alertIntervals->pluck('minute_before_event')->toArray(),
                $event->tags->pluck('name')->toArray()
            );
        })->all();
    }

    public function getEditDtoById(EventId $eventId, EventUserId $eventUserId): EventEditDto
    {
        $event = EventEloquent::with(['alertIntervals', 'tags'])
            ->where('id', $eventId->getValue())
            ->where('user_id', $eventUserId->getValue())
            ->whereHas('tags', function ($q) use ($eventUserId) {
                $q->where('user_id', $eventUserId->getValue());
            })
            ->firstOrFail();
        return new EventEditDto(
            $event->id,
            $event->name,
            $event->event_date->format('Y-m-d H:i'),
            $event->impression ?? '',
            $event->alertIntervals->map(
                fn($a) => ['minute_before_event' => $a->minute_before_event]
            )->toArray(),
            $event->tags->pluck('id')->toArray()
        );
    }

    public function findById(EventId $id): ?Event
    {
        $eloquentEvent = EventEloquent::find($id->getValue());
        if (!$eloquentEvent) {
            return null;
        }
        return new Event(
            new EventUserId($eloquentEvent->user_id),
            new EventName($eloquentEvent->name),
            new EventDate($eloquentEvent->event_date),
            new EventImpression($eloquentEvent->impression),
            new EventId($eloquentEvent->id)
        );
    }

    public function create(Event $Event): EventId
    {
        $eloquentEvent = new EventEloquent();
        $eloquentEvent->user_id = $Event->getUserId()->getValue();        
        $eloquentEvent->name = $Event->getEventName()->getValue();
        $eloquentEvent->event_date = $Event->getEventDate()->getValue();
        $eloquentEvent->impression = $Event->getEventImpression()->getValue();
        $eloquentEvent->save();
        return new EventId($eloquentEvent->id);
    }

    public function update(Event $Event): bool
    {
        $eloquentEvent = EventEloquent::find($Event->getId()->getValue());
        if (!$eloquentEvent) {
            return false;
        }
        $eloquentEvent->user_id = $Event->getUserId()->getValue();        
        $eloquentEvent->name = $Event->getEventName()->getValue();
        $eloquentEvent->event_date = $Event->getEventDate()->getValue();
        $eloquentEvent->impression = $Event->getEventImpression()->getValue();
        return $eloquentEvent->save();
    }

    public function delete(EventId $id): bool
    {
        return EventEloquent::destroy($id->getValue());
    }

    public function attachTags(EventId $id, array $tagIds): void
    {
        $eloquentEvent = EventEloquent::find($id->getValue());
        $eloquentEvent->tags()->detach();
        $eloquentEvent->tags()->attach($tagIds);
    }

    public function detachTags(EventId $id): void
    {
        $eloquentEvent = EventEloquent::find($id->getValue());
        if ($eloquentEvent) {
            $eloquentEvent->tags()->detach(); // 中間テーブル削除
        }
    }   
}