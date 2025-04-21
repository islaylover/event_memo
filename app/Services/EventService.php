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
use App\Domain\Repositories\TagRepositoryInterface;
use App\Infrastructure\Eloquent\AlertIntervalEloquent;
use App\Domain\Dto\EventEditDto;
use App\Domain\Dto\AlertNotificationDto;
use App\Domain\Models\Tag\Tag;
use App\Domain\Models\Tag\TagUserId;
use App\Domain\Models\Tag\TagName;
use App\Domain\Services\TagDomainService;
use App\Domain\Services\AlertIntervalDomainService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class EventService
{
    protected $EventRepository;
    protected $AlertIntervalRepository;
    protected $TagRepository;

    public function __construct(
        EventRepositoryInterface $EventRepository, 
        AlertIntervalRepositoryInterface $AlertIntervalRepository,
        TagRepositoryInterface $TagRepository
    ) {
        $this->EventRepository = $EventRepository;
        $this->AlertIntervalRepository = $AlertIntervalRepository;
        $this->TagRepository = $TagRepository;
    }

    public function getAllEventSummaries(EventUserId $eventUserId): array
    {
        return $this->EventRepository->getAllEventSummaries($eventUserId);
    }

    public function getEventEditDto($id, $eventUserId): EventEditDto
    {
        return $this->EventRepository->getEditDtoById(
            new EventId($id),
            new EventUserId($eventUserId)
        );
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
     * @throws InvalidArgumentException データが不正な場合（バリデーションエラーなど）
     * @throws Exception DB保存失敗やその他の予期せぬエラー
     */
    public function createEvent(array $data): void
    {
        DB::beginTransaction();
        try {
            $Event = new Event(
                new EventUserId($data['user_id']),
                new EventName($data['name']),
                new EventDate($data['event_date']),
                new EventImpression($data['impression'])
            );
            $eventId = $this->EventRepository->create($Event);
            $data['alert_intervals'] = AlertIntervalDomainService::removeDuplicates($data['alert_intervals']);
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
            $this->syncTags(
                $eventId,
                $data['tag_ids'] ?? [],
                $data['new_tag_name'] ?? []
            );
            
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * イベント更新
     * 
     * @param array $data
     * @throws InvalidArgumentException データが不正な場合（バリデーションエラーなど）
     * @throws Exception DB保存失敗やその他の予期せぬエラー
     */
    public function updateEvent(array $data): void
    {
        DB::beginTransaction();
        try {
            $existing = $this->EventRepository->findById(new EventId($data['id']));
            if (!$existing || $existing->getUserId()->getValue() !== Auth::id()) {
                throw new \Exception("権限がありません");
            }
            $Event = new Event(
                new EventUserId($data['user_id']),
                new EventName($data['name']),
                new EventDate($data['event_date']),
                new EventImpression($data['impression']),
                new EventId($data['id'])
            );
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
                    Log::info("-- save alert interval --");
                    $this->AlertIntervalRepository->create($AlertInterval);
                }
            }
            $this->syncTags(
                $eventId,
                $data['tag_ids'] ?? [],
                $data['new_tag_name'] ?? []
            );
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

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
                $event->event_date->format('Y-m-d H:i'),
                $user->name,
                $user->email,
                $alert->minute_before_event,
                $event->tags->pluck('name')->toArray()
            );
        })->all();
    }

    /**
     * タグをイベントに関連付け（新規作成 + 既存統合 + 重複排除）
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
}