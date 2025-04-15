<?php

namespace App\Domain\Repositories;

use App\Domain\Models\AlertInterval\AlertInterval;
use App\Domain\Models\AlertInterval\AlertIntervalId;
use App\Domain\Models\Event\EventId;

interface AlertIntervalRepositoryInterface
{
    public function getAll() :array;
    public function findById(AlertIntervalId $alert_interval_id): ?AlertInterval;
    public function create(AlertInterval $event): bool;
    public function update(AlertInterval $event):bool;
    public function delete(AlertIntervalId $alert_interval_id) :bool;
    public function deleteByEventId(EventId $id): bool;
}