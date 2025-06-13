<?php

namespace App\Services;

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
use App\Domain\Models\AlertInterval\AlertInterval;
use App\Domain\Models\AlertInterval\AlertIntervalMinuteBeforeEvent;
use App\Domain\Repositories\AlertIntervalRepositoryInterface;
use App\Domain\Repositories\TagRepositoryInterface;
use App\Infrastructure\Eloquent\AlertIntervalEloquent;
use App\Domain\Dto\EventSummaryDto;
use App\Domain\Dto\EventEditDto;
use App\Domain\Dto\AlertNotificationDto;
use App\Domain\Models\Tag\Tag;
use App\Domain\Models\Tag\TagUserId;
use App\Domain\Models\Tag\TagName;
use App\Domain\Services\AlertIntervalDomainService;
use App\Domain\Services\EventDomainService;
use App\Domain\Services\TagDomainService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use App\Infrastructure\External\GoogleCalendarApiClient;


use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class EventService
{
    protected $EventRepository;
    protected $AlertIntervalRepository;
    protected $TagRepository;
    protected $GoogleCalendarApiClient;

    public function __construct(
        EventRepositoryInterface $EventRepository, 
        AlertIntervalRepositoryInterface $AlertIntervalRepository,
        TagRepositoryInterface $TagRepository,
        GoogleCalendarApiClient $GoogleCalendarApiClient
    ) {
        $this->EventRepository = $EventRepository;
        $this->AlertIntervalRepository = $AlertIntervalRepository;
        $this->TagRepository = $TagRepository;
        $this->GoogleCalendarApiClient = $GoogleCalendarApiClient;
    }

    public function getAllEventSummaries(EventUserId $eventUserId): array
    {
        $events = $this->EventRepository->getAllEventsByUserId($eventUserId);
        return $events->map(fn($event) => new EventSummaryDto($event))->all();
    }

    public function getEventDetail($id, $eventUserId): EventEditDto
    {
        $event = $this->EventRepository->getEventDetail(
            new EventId($id),
            new EventUserId($eventUserId)
        );
        return new EventEditDto($event);
    }

    public function getAllTags($eventUserId): array
    {
        $TagUserId = new TagUserId($eventUserId);
        return $this->TagRepository->getAllByUserId($TagUserId);
    }

    /**
     * イベント新規作成
     * 
     * @param array $data
     * @return Event
     * @throws InvalidArgumentException データが不正な場合（バリデーションエラーなど）
     * @throws Exception DB保存失敗やその他の予期せぬエラー
     */
    public function createEvent(array $data): Event
    {
        DB::beginTransaction();
        try {
            $Event = new Event(
                new EventUserId($data['user_id']),
                new EventName($data['name']),
                new EventDate($data['event_date']),
                new EventEndDate($data['event_end_date']),
                new EventMemo($data['memo']),
                new EventImpression($data['impression']),
                new GoogleEventId('')
            );
            EventDomainService::validateEventDateRange($Event);
            $eventId = $this->EventRepository->create($Event);
            // IDをセットして返却用Eventを完成させる
            $Event->setId($eventId);
            $data['alert_intervals'] = AlertIntervalDomainService::removeDuplicates($data['alert_intervals']);
            if (!empty($data['alert_intervals'])) {
                foreach ($data['alert_intervals'] as $eachAlertInterval) {
                    $AlertInterval = new AlertInterval(
                        $eventId,
                        new AlertIntervalMinuteBeforeEvent($eachAlertInterval['minute_before_event'])
                    );
                    $this->AlertIntervalRepository->create($AlertInterval);
                }
            }
            $this->syncTags(
                $eventId,
                $data['tag_ids'] ?? [],
                $data['new_tag_name'] ?? []
            );
            
            DB::commit();
            return $Event;

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * イベント更新
     * 
     * @param array $data
     * @return Event
     * @throws InvalidArgumentException データが不正な場合（バリデーションエラーなど）
     * @throws Exception DB保存失敗やその他の予期せぬエラー
     */
    public function updateEvent(array $data): Event
    {
        DB::beginTransaction();
        try {
            $existingEvent = $this->EventRepository->findById(new EventId($data['id']));
            if (!$existingEvent || $existingEvent->getUserId()->getValue() !== Auth::id()) {
                throw new \Exception("権限がありません");
            }
            $Event = new Event(
                new EventUserId($data['user_id']),
                new EventName($data['name']),
                new EventDate($data['event_date']),
                new EventEndDate($data['event_end_date']),
                new EventMemo($data['memo']),                
                new EventImpression($data['impression']),
                $existingEvent->getGoogleEventId(),
                new EventId($data['id'])
            );
            EventDomainService::validateEventDateRange($Event);
            $this->EventRepository->update($Event);
            $eventId = new EventId($data['id']);
            $this->AlertIntervalRepository->deleteByEventId($eventId);
            $data['alert_intervals'] = AlertIntervalDomainService::removeDuplicates($data['alert_intervals']);
            if (!empty($data['alert_intervals'])) {
                foreach ($data['alert_intervals'] as $eachAlertInterval) {
                    $AlertInterval = new AlertInterval(
                        $eventId,
                        new AlertIntervalMinuteBeforeEvent($eachAlertInterval['minute_before_event'])
                    );
                    $this->AlertIntervalRepository->create($AlertInterval);
                }
            }
            $this->syncTags(
                $eventId,
                $data['tag_ids'] ?? [],
                $data['new_tag_name'] ?? []
            );
            DB::commit();
            return $Event;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * イベント削除
     * 
     * @param int $id
     * @return boolean 削除が成功した場合は true を返す
     * @throws Exception 削除権限がない場合、またはDBエラーなどが発生した場合
     */
    public function deleteEvent($id): bool
    {
        $event = $this->EventRepository->findById(new EventId($id));
        if (!$event || $event->getUserId()->getValue() !== Auth::id()) {
            throw new \Exception("削除権限がありません");
        }
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

    public function getAlertNotifications(Carbon $now): array
    {
        $alerts = $this->AlertIntervalRepository->findAlertsForNotification($now);
        return $alerts->map(function ($alert) {
            $event = $alert->event;
            $user = $event->user;
            return new AlertNotificationDto(
                $event->id,
                $event->name,
                $event->event_date,
                $event->event_end_date,
                $event->memo,
                $user->name,
                $user->email,
                $alert->minute_before_event,
                $event->tags->pluck('name')->toArray() ?? ''
            );
        })->all();
    }

    /**
     * タグをイベントに関連付け（新規作成 + 既存統合 + 重複排除）
     * 
     * @param EventId $eventId イベントID
     * @param array $tagIds 既存タグIDの配列
     * @param array $newTagNames 新規タグ名の配列
     * @return void
     */
    private function syncTags(EventId $eventId, array $tagIds = [], array $newTagNames = []): void
    {
        $tagEntities = [];
        foreach ($newTagNames as $tagName) {
            $trimmed = trim($tagName);
            if ($trimmed !== '') {
                $tagEntities[] = new Tag(new TagUserId(Auth::id()), new TagName($trimmed));
            }
        }
        $tagEntities = TagDomainService::removeDuplicateEntities($tagEntities);

        $persistedTagIds = [];
        foreach ($tagEntities as $tagEntity) {
            $persistedTagIds[] = $this->TagRepository->firstOrCreate($tagEntity)->getValue();
        }
        $mergedTagIds = array_merge($tagIds, $persistedTagIds);
        $finalTagIds = TagDomainService::removeDuplicateIds($mergedTagIds);

        if (!empty($finalTagIds)) {
            $this->EventRepository->attachTags($eventId, $finalTagIds);
        }
    }

    /**
     * Google カレンダーに登録したイベントの ID を events テーブルに保存する
     *
     * @param EventId $eventId イベントID
     * @param string|null $googleEventId Google カレンダーイベントID（null の場合は削除扱い）
     * @return void
     */
    public function updateGoogleEventId(EventId $eventId, ?string $googleEventId): void
    {
        $this->EventRepository->updateGoogleEventId($eventId, $googleEventId);
    }

    /**
     * 指定された ID に対応するイベントを取得する
     *
     * @param int $id イベントID（プリミティブ整数型）
     * @return Event イベントエンティティ（見つからない場合は null ではなく例外やnull対応は設計次第）
     */
    public function getEventById($id): Event
    {
        return $this->EventRepository->findById(new EventId($id));
    }

    /** 
     * イベント情報をAPI経由でGoogle Calendarと連動して登録/更新/削除する
     * 
     * @param  User $user
     * @param  Event $Event
     * @param  boolean $shouldSync
     * @return void
     */
    public function syncWithGoogleCalendar(User $user, Event $event, bool $shouldSync): void
    {
        if ($shouldSync) {
            $start = $event->getEventDate()->toCarbon();
            $end = ($event->getEventEndDate()->getValue()) 
                ? $event->getEventEndDate()->toCarbon()
                : $start->copy()->addHour();
            $description = $event->getEventMemo()->getValue() ?? '';
        }
        $googleEventId = $event->getGoogleEventId() 
                ? $event->getGoogleEventId()->getValue() 
                : null;
        if ($shouldSync && $googleEventId) {
            // 更新
            $this->GoogleCalendarApiClient->updateEvent($user, $googleEventId, $event->getEventName(), $description, $start, $end);
        } elseif ($shouldSync && !$googleEventId) {
            // 新規作成
            $newId = $this->GoogleCalendarApiClient->createEvent($user, $event->getEventName(), $description, $start, $end);
            $this->updateGoogleEventId($event->getId(), $newId);
        } elseif (!$shouldSync && $googleEventId) {
            // 削除
            $this->GoogleCalendarApiClient->deleteEvent($user, $googleEventId);
            $this->updateGoogleEventId($event->getId(), null);
        }
    }

    /** 
     * イベント情報をAPI経由でGoogle Calendarと連動して登録/更新/削除する
     *
     * @param  User $user
     * @param  Event $Event
     * @return void
    */
    public function deleteFromGoogleCalendar(User $user, Event $event): void
    {
       $googleEventId = $event->getGoogleEventId() 
                ? $event->getGoogleEventId()->getValue() 
                : null;
        if (!empty($googleEventId)) {
            $this->GoogleCalendarApiClient->deleteEvent($user, $googleEventId);
        }
    }
}