<?php 

namespace App\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Collection;
use App\Domain\Models\Event\Event;
use App\Domain\Models\Event\EventId;
use App\Domain\Models\Event\EventUserId;
use App\Domain\Models\Event\EventName;
use App\Domain\Models\Event\EventDate;
use App\Domain\Models\Event\EventEndDate;
use App\Domain\Models\Event\EventMemo;
use App\Domain\Models\Event\EventImpression;
use App\Domain\Models\Event\GoogleEventId;
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
                new EventEndDate($eloquentEvent->event_end_date),
                new EventMemo($eloquentEvent->memo),
                new EventImpression($eloquentEvent->impression),
                new GoogleEventId($eloquentEvent->google_event_id),
                new EventId($eloquentEvent->id)
            );
        })->all();
    }

    public function getAllEventsByUserId(EventUserId $eventUserId): Collection
    {
        $userId = $eventUserId->getValue();
        return EventEloquent::with(['tags', 'alertIntervals'])
            ->where('user_id', $userId)
            ->orderBy('event_date', 'asc')
            ->get();
    }

    public function getEventDetail(EventId $eventId, EventUserId $eventUserId): EventEloquent
    {
        return EventEloquent::with(['alertIntervals', 'tags'])
            ->where('id', $eventId->getValue())
            ->where('user_id', $eventUserId->getValue())
            ->firstOrFail();
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
            new EventEndDate($eloquentEvent->event_end_date),
            new EventMemo($eloquentEvent->memo),
            new EventImpression($eloquentEvent->impression),
            new GoogleEventId($eloquentEvent->google_event_id),
            new EventId($eloquentEvent->id)
        );
    }

    public function create(Event $Event): EventId
    {
        $eloquentEvent = new EventEloquent();
        $eloquentEvent->user_id = $Event->getUserId()->getValue();        
        $eloquentEvent->name = $Event->getEventName()->getValue();
        $eloquentEvent->event_date = $Event->getEventDate()->getValue();
        $eloquentEvent->event_end_date = $Event->getEventEndDate()->getValue();
        $eloquentEvent->memo = $Event->getEventMemo()->getValue();
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
        $eloquentEvent->event_end_date = $Event->getEventEndDate()->getValue();
        $eloquentEvent->memo = $Event->getEventMemo()->getValue();
        $eloquentEvent->impression = $Event->getEventImpression()->getValue();
        $eloquentEvent->google_event_id = $Event->getGoogleEventId()->getValue();
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

    public function updateGoogleEventId(EventId $eventId, ?string $googleEventId): void
    {
        $event = EventEloquent::findOrFail($eventId->getValue());
        $event->google_event_id = $googleEventId;
        $event->save();
    }
}