<?php 

namespace App\Infrastructure\Repositories;

use App\Domain\Models\Event\Event;
use App\Domain\Models\Event\EventId;
use App\Domain\Models\Event\EventName;
use App\Domain\Models\Event\EventDate;
use App\Domain\Models\Event\EventImpression;

use App\Domain\Repositories\EventRepositoryInterface;
use App\Infrastructure\Eloquent\EventEloquent;
use Illuminate\Support\Facades\Log;

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
        Log::info("EloquentEventRepository update step1");
        $eloquentEvent = EventEloquent::find($Event->getId()->getValue());
        if (!$eloquentEvent) {
            return false;
        }
        Log::info("EloquentEventRepository update step2");
        $eloquentEvent->user_id = $Event->getUserId()->getValue();        
        $eloquentEvent->name = $Event->getEventName()->getValue();
        $eloquentEvent->event_date = $Event->getEventDate()->getValue();
        $eloquentEvent->impression = $Event->getEventImpression()->getValue();
        Log::info("EloquentEventRepository update step2-2");
        return $eloquentEvent->save();
    }

    public function delete(EventId $id): bool
    {
        return EventEloquent::destroy($id->getValue());
    }

    public function attachTags(EventId $id, array $tagIds): void
    {
        $eloquentEvent = EventEloquent::find($id->getValue());
        $eloquentEvent->tags()->sync($tagIds); // ← 中間テーブル「event_tag」に保存
    }

    public function detachTags(EventId $id): void
    {
        $eloquentEvent = EventEloquent::find($id->getValue());
        if ($eloquentEvent) {
            $eloquentEvent->tags()->detach(); // 中間テーブル削除
        }
    }   
}