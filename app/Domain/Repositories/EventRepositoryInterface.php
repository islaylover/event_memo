<?php

namespace App\Domain\Repositories;

use App\Domain\Models\Event\Event;
use App\Domain\Models\Event\EventId;
use App\Domain\Models\Event\EventUserId;
use App\Domain\Dto\EventEditDto;

interface EventRepositoryInterface
{
    public function getAll() :array;
    public function getAllEventSummaries(EventUserId $eventUserId): array;
    public function getEditDtoById(EventId $eventId, EventUserId $userId): EventEditDto;
    public function findById(EventId $event_id): ?Event;
    public function create(Event $event): EventId;
    public function update(Event $event):bool;
    public function delete(EventId $event_id) :bool;
    public function attachTags(EventId $id, array $tagIds): void;
    public function detachTags(EventId $id): void;
}
