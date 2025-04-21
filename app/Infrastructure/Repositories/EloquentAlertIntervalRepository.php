<?php 

namespace App\Infrastructure\Repositories;

use App\Domain\Models\AlertInterval\AlertInterval;
use App\Domain\Models\AlertInterval\AlertIntervalId;
use App\Domain\Models\Event\EventId;
use App\Domain\Models\AlertInterval\AlertIntervalMinuteBeforeEvent;

use App\Domain\Repositories\AlertIntervalRepositoryInterface;
use App\Infrastructure\Eloquent\AlertIntervalEloquent;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class EloquentAlertIntervalRepository implements AlertIntervalRepositoryInterface
{

    public function getAll(): array
    {
        return AlertIntervalEloquent::all()->map(function ($eloquentAlertInterval) {
            return new AlertInterval(
                new EventId($eloquentAlertInterval->event_id),
                new AlertIntervalMinuteBeforeEvent($eloquentAlertInterval->minute_before_event),
                new AlertIntervalId($eloquentAlertInterval->id)
            );
        })->all();//all() : convert result(collection) to array
    }

    public function findById(AlertIntervalId $id): ?AlertInterval
    {
        $eloquentAlertInterval = AlertIntervalEloquent::find($id->getValue());
        if (!$eloquentAlertInterval) {
            return null;
        }
        return new AlertInterval(
            new EventId($eloquentAlertInterval->event_id),
            new AlertIntervalMinuteBeforeEvent($eloquentAlertInterval->minute_before_event),
            new AlertIntervalId($eloquentAlertInterval->id)
        );
    }

    public function create(AlertInterval $AlertInterval): bool
    {
        $eloquentAlertInterval = new AlertIntervalEloquent();
        $eloquentAlertInterval->event_id            = $AlertInterval->getEventId()->getValue();        
        $eloquentAlertInterval->minute_before_event = $AlertInterval->getAlertIntervalMinuteBeforeEvent()->getValue();
        return $eloquentAlertInterval->save();
    }

    public function update(AlertInterval $AlertInterval): bool
    {
        $eloquentAlertInterval = AlertIntervalEloquent::find($AlertInterval->getId()->getValue());
        if (!$eloquentAlertInterval) {
            return false;
        }
        $eloquentAlertInterval->event_id            = $AlertInterval->getEventId()->getValue();        
        $eloquentAlertInterval->minute_before_event = $AlertInterval->getAlertIntervalMinuteBeforeEvent()->getValue();
        return $eloquentAlertInterval->save();
    }

    public function delete(AlertIntervalId $id): bool
    {
        return AlertIntervalEloquent::destroy($id->getValue());
    }

    public function deleteByEventId(EventId $id): bool
    {
        return AlertIntervalEloquent::where('event_id', $id->getValue())->delete();
    }

    public function findAlertsForNotification(Carbon $now): Collection
    {
        return AlertIntervalEloquent::with('event.user', 'event.tags')
            ->whereHas('event', function ($query) use ($now) {
                $query->whereRaw("DATE_SUB(events.event_date, INTERVAL alert_intervals.minute_before_event MINUTE) BETWEEN ? AND ?", [
                    $now->format('Y-m-d H:i:00'),
                    $now->copy()->addMinute()->format('Y-m-d H:i:59')
                ]);
            })
            ->get();
    }

}