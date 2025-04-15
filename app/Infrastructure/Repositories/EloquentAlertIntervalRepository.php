<?php 

namespace App\Infrastructure\Repositories;

use App\Domain\Models\AlertInterval\AlertInterval;
use App\Domain\Models\AlertInterval\AlertIntervalId;
use App\Domain\Models\Event\EventId;
use App\Domain\Models\AlertInterval\AlertIntervalMinuteBeforeEvent;

use App\Domain\Repositories\AlertIntervalRepositoryInterface;
use App\Infrastructure\Eloquent\AlertIntervalEloquent;
use Illuminate\Support\Facades\Log;

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
        Log::info("EloquentAlertIntervalRepository update step1");
        $eloquentAlertInterval = AlertIntervalEloquent::find($AlertInterval->getId()->getValue());
        if (!$eloquentAlertInterval) {
            return false;
        }
        Log::info("EloquentAlertIntervalRepository update step2");
        $eloquentAlertInterval->event_id            = $AlertInterval->getEventId()->getValue();        
        $eloquentAlertInterval->minute_before_event = $AlertInterval->getAlertIntervalMinuteBeforeEvent()->getValue();
        Log::info("EloquentAlertIntervalRepository update step2-2");
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
}