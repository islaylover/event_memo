<?php

namespace App\Domain\Repositories;

use App\Domain\Models\AlertInterval\AlertInterval;
use App\Domain\Models\AlertInterval\AlertIntervalId;
use App\Domain\Models\Event\EventId;
use Illuminate\Support\Collection;
use Carbon\Carbon;

interface AlertIntervalRepositoryInterface
{
    public function getAll() :array;
    public function findById(AlertIntervalId $alertIntervalId): ?AlertInterval;
    public function create(AlertInterval $event): bool;
    public function update(AlertInterval $event):bool;
    public function delete(AlertIntervalId $alertIntervalId) :bool;
    public function deleteByEventId(EventId $id): bool;
    public function findAlertsForNotification(Carbon $now): Collection;
}