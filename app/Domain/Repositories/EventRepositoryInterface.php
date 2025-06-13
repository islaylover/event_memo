<?php

namespace App\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;
use App\Domain\Models\Event\Event;
use App\Domain\Models\Event\EventId;
use App\Domain\Models\Event\EventUserId;
use App\Domain\Dto\EventEditDto;
use App\Infrastructure\Eloquent\EventEloquent;

interface EventRepositoryInterface
{
    public function getAll() :array;
    public function getAllEventsByUserId(EventUserId $eventUserId): Collection;
    public function getEventDetail(EventId $eventId, EventUserId $userId): EventEloquent;
    public function findById(EventId $event_id): ?Event;
    public function create(Event $event): EventId;
    public function update(Event $event):bool;
    public function delete(EventId $event_id) :bool;
    public function attachTags(EventId $id, array $tagIds): void;
    public function detachTags(EventId $id): void;
    public function updateGoogleEventId(EventId $eventId, ?string $googleEventId): void;
}
