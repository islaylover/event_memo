<?php

namespace App\Domain\Repositories;

use App\Domain\Models\Event\Event;
use App\Domain\Models\Event\EventId;

interface EventRepositoryInterface
{
    public function getAll() :array;
    public function findById(EventId $event_id): ?Event;
    public function create(Event $event): EventId;
    public function update(Event $event):bool;
    public function delete(EventId $event_id) :bool;
    public function attachTags(EventId $id, array $tagIds): void;
    public function detachTags(EventId $id): void;
}
