<?php

namespace App\Services;

use App\Domain\Models\Event\Event;
use App\Domain\Models\Event\EventId;
use App\Domain\Models\Event\EventUserId;
use App\Domain\Models\Event\EventName;
use App\Domain\Models\Event\EventDate;
use App\Domain\Models\Event\EventImpression;
use App\Domain\Repositories\EventRepositoryInterface;
use App\Domain\Models\AlertInterval\AlertInterval;
use App\Domain\Models\AlertInterval\AlertIntervalMinuteBeforeEvent;
use App\Domain\Repositories\AlertIntervalRepositoryInterface;
use App\Infrastructure\Eloquent\EventEloquent;
//use App\Http\Traits\CommonFunctions;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class EventService
{
    protected $EventRepository;
    protected $AlertIntervalRepository;

    public function __construct(EventRepositoryInterface $EventRepository, AlertIntervalRepositoryInterface $AlertIntervalRepository)
    {
        $this->EventRepository = $EventRepository;
        $this->AlertIntervalRepository = $AlertIntervalRepository;
    }

    public function getAllEvents()
    {
        return $this->EventRepository->getAll();
    }

    public function getAllEventsWithRelations()
    {
        return EventEloquent::with(['tags', 'alertIntervals'])->orderBy('event_date', 'asc')->get();
    }

    public function getEventById(int $id): ?Event
    {
        return $this->EventRepository->findById(new EventId($id));
    }

    public function getEventWithRelations($id)
    {
        return EventEloquent::with(['alertIntervals', 'tags'])->findOrFail($id);
    }

    public function createEvent(array $data)
    {
        DB::beginTransaction();
        try {
            Log::info("EventRepository createEvent step 1 save events");
            $Event = new Event(
                new EventUserId($data['user_id']),
                new EventName($data['name']),
                new EventDate($data['event_date']),
                new EventImpression($data['impression'])
            );
            $eventId = $this->EventRepository->create($Event);
            Log::info("EventRepository createEvent step 2 save alter_intervals");
            if (!empty($data['alert_intervals'])) {
                foreach ($data['alert_intervals'] as $eachAlertInterval) {
                    Log::info("-- each alert interval -- ". $eachAlertInterval['minute_before_event']);
                    $AlertInterval = new AlertInterval(
                        $eventId,
                        new AlertIntervalMinuteBeforeEvent($eachAlertInterval['minute_before_event'])
                    );
                    $this->AlertIntervalRepository->create($AlertInterval);
                }
            }
            Log::info("EventRepository createEvent step 3 save tags");
            if (!empty($data['tag_ids'])) {
                $this->EventRepository->attachTags($eventId, $data['tag_ids']); // 後述
            }
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateEvent(array $data)
    {
        DB::beginTransaction();
        try {
            Log::info("EventRepository updateEvent step 1 update events");
            $Event = new Event(
                new EventUserId($data['user_id']),
                new EventName($data['name']),
                new EventDate($data['event_date']),
                new EventImpression($data['impression']),
                new EventId($data['id'])
            );
            $this->EventRepository->update($Event);
            $eventId = new EventId($data['id']);
            Log::info("EventRepository updateEvent step 2 update alter_intervals");
            $this->AlertIntervalRepository->deleteByEventId($eventId);
            if (!empty($data['alert_intervals'])) {
                foreach ($data['alert_intervals'] as $eachAlertInterval) {
                    $AlertInterval = new AlertInterval(
                        $eventId,
                        new AlertIntervalMinuteBeforeEvent($eachAlertInterval['minute_before_event'])
                    );
                    Log::info("-- save alert interval --");
                    $this->AlertIntervalRepository->create($AlertInterval);
                }
            }
            Log::info("EventRepository updateEvent step 3 update tags");
            if (!empty($data['tag_ids'])) {
                $this->EventRepository->attachTags($eventId, $data['tag_ids']);
            }
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteEvent($id): bool
    {
        DB::beginTransaction();
        try {
            $eventId = new EventId($id);
            // alert_intervals 削除
            $this->AlertIntervalRepository->deleteByEventId($eventId);
            // tags（中間テーブル event_tag）解除
            $this->EventRepository->detachTags($eventId);
            // events 削除
            $this->EventRepository->delete($eventId);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

}