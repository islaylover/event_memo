<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use Mockery;
use App\Services\EventService;
use App\Domain\Models\Event\Event;
use App\Domain\Models\Event\EventId;
use App\Domain\Models\Event\EventUserId;
use App\Domain\Models\Event\EventName;
use App\Domain\Models\Event\EventDate;
use App\Domain\Models\Event\EventEndDate;
use App\Domain\Models\Event\EventMemo;
use App\Domain\Models\Event\EventImpression;
use App\Domain\Models\Event\GoogleEventId;
use App\Domain\Models\AlertInterval\AlertInterval;
use App\Domain\Models\AlertInterval\AlertIntervalMinuteBeforeEvent;
use App\Domain\Models\Tag\Tag;
use App\Domain\Models\Tag\TagId;
use App\Domain\Models\Tag\TagUserId;
use App\Domain\Models\Tag\TagName;
use App\Domain\Repositories\EventRepositoryInterface;
use App\Domain\Repositories\AlertIntervalRepositoryInterface;
use App\Domain\Repositories\TagRepositoryInterface;
use App\Infrastructure\External\GoogleCalendarApiClient;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class EventServiceTest extends TestCase
{
    protected $eventRepo;
    protected $alertIntervalRepo;
    protected $tagRepo;
    protected $googleApi;
    protected $eventService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventRepo = Mockery::mock(EventRepositoryInterface::class);
        $this->alertIntervalRepo = Mockery::mock(AlertIntervalRepositoryInterface::class);
        $this->tagRepo = Mockery::mock(TagRepositoryInterface::class);
        $this->googleApi = Mockery::mock(GoogleCalendarApiClient::class);

        Auth::shouldReceive('id')->andReturn(123);

        $this->eventService = new EventService(
            $this->eventRepo,
            $this->alertIntervalRepo,
            $this->tagRepo,
            $this->googleApi
        );
    }

    public function testCreateEventWithoutGoogleSync()
    {
        $this->eventRepo->shouldReceive('create')->once()->andReturn(new EventId(1));
        $this->alertIntervalRepo->shouldReceive('create')->twice();
        $this->tagRepo->shouldReceive('firstOrCreate')->andReturn(
            new TagId(5)
        );
        $this->eventRepo->shouldReceive('attachTags')->once();

        $data = [
            'user_id' => 1,
            'name' => 'テストイベント',
            'event_date' => '2025-06-15 10:00',
            'event_end_date' => '2025-06-15 11:00',
            'memo' => 'このメンバーの飲み会は危険', 
            'impression' => 'とても楽しかった',
            'alert_intervals' => [
                ['minute_before_event' => 10],
                ['minute_before_event' => 5]
            ],
            'tag_ids' => [1, 2],
            'new_tag_name' => ['仕事']
        ];

        $event = $this->eventService->createEvent($data);

        $this->assertInstanceOf(Event::class, $event);
        $this->assertEquals('テストイベント', $event->getEventName()->getValue());
    }

    public function testUpdateEventWithoutGoogleSync()
    {
        Auth::shouldReceive('id')->andReturn(123);

        //旧テストデータ
        $eventId = new EventId(1);
        $existingEvent = new Event(
            new EventUserId(123),
            new EventName('旧イベント'),
            new EventDate('2025-06-10 10:00'),
            new EventEndDate('2025-06-10 11:00'),
            new EventMemo('旧メモ'),            
            new EventImpression('旧感想'),
            new GoogleEventId(''), // Google連携なし
            $eventId
        );

        $this->eventRepo->shouldReceive('findById')
            ->once()
            ->withArgs(function ($arg) {
                return $arg instanceof EventId && $arg->getValue() === 1;
            })
            ->andReturn($existingEvent);

        $this->eventRepo->shouldReceive('update')->once();

        $this->alertIntervalRepo->shouldReceive('deleteByEventId')
            ->once()
            ->withArgs(function ($arg) {
                return $arg instanceof EventId && $arg->getValue() === 1;
            });

        $this->alertIntervalRepo->shouldReceive('create')->twice();

        $this->tagRepo->shouldReceive('firstOrCreate')->andReturn(new TagId(5));
        $this->eventRepo->shouldReceive('attachTags')->once();

        // テストデータ（更新内容）
        $data = [
            'id' => 1,
            'user_id' => 123,
            'name' => '更新イベント',
            'event_date' => '2025-06-20 15:00',
            'event_end_date' => '2025-06-20 16:00',
            'memo' => '更新メモ',
            'impression' => '更新感想',
            'alert_intervals' => [
                ['minute_before_event' => 30],
                ['minute_before_event' => 10],
            ],
            'tag_ids' => [3, 4],
            'new_tag_name' => ['仕事']
        ];

        $updated = $this->eventService->updateEvent($data);

        $this->assertInstanceOf(Event::class, $updated);
        $this->assertEquals('更新イベント', $updated->getEventName()->getValue());
        $this->assertEquals('更新感想', $updated->getEventImpression()->getValue());
    }

    public function testUpdateEventRollbackOnException()
    {
        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        $this->expectException(\InvalidArgumentException::class);

        $this->eventRepo->shouldReceive('findById')
            ->once()
            ->withArgs(function ($arg) {
                return $arg instanceof EventId && $arg->getValue() === 1;
            })
            ->andReturn(new Event(
                new EventUserId(123),
                new EventName('旧イベント'),
                new EventDate('2025-06-10 10:00'),
                new EventEndDate('2025-06-10 11:00'),
                new EventMemo('旧メモ'),    
                new EventImpression('旧感想'),
                new GoogleEventId(''),
                new EventId(1)
            ));

        // 不正データ（EventNameがバリデーションエラー）
        $data = [
            'id' => 1,
            'user_id' => 123,
            'name' => str_repeat('あ', 100), // ← InvalidArgumentExceptionを発生させる
            'event_date' => '2025-06-20 15:00',
            'event_end_date' => '2025-06-20 16:00',
            'memo' => '更新メモ',
            'impression' => '更新感想',
            'alert_intervals' => [],
            'tag_ids' => [],
            'new_tag_name' => [],
        ];

        $this->eventService->updateEvent($data);
    }

    public function testSyncWithGoogleCalendarCreatesAndSavesGoogleEventId()
    {
        $user = new User([
            'google_access_token' => 'dummy_token',
            'google_refresh_token' => 'dummy_refresh',
        ]);

        $eventId = new EventId(1);
        $event = new Event(
            new EventUserId(123),
            new EventName('Google連携イベント'),
            new EventDate('2025-06-30 10:00'),
            new EventEndDate('2025-06-30 11:00'),
            new EventMemo('Google同期テスト'),            
            new EventImpression('Google同期できた'),
            new GoogleEventId(''), // 空 → createEvent()
            $eventId
        );

        $this->googleApi->shouldReceive('createEvent')
            ->once()
            ->andReturn('s772kpn3nokcs896kbaio1ui1g');

        $this->eventRepo->shouldReceive('updateGoogleEventId')
            ->once()
            ->with($eventId, 's772kpn3nokcs896kbaio1ui1g');

        $this->eventService->syncWithGoogleCalendar($user, $event, true);
    }

    public function testUpdateEventDisableGoogleSyncDeletesFromGoogleCalendar()
    {
        Auth::shouldReceive('id')->andReturn(123);

        $eventId = new EventId(1);
        $googleEventId = new GoogleEventId('s772kpn3nokcs896kbaio1ui1g');

        //旧データ
        $existingEvent = new Event(
            new EventUserId(123),
            new EventName('旧イベント'),
            new EventDate('2025-06-10 10:00'),
            new EventEndDate('2025-06-10 11:00'),
            new EventMemo('旧メモ'),
            new EventImpression('旧感想'),
            $googleEventId,
            $eventId
        );

        $this->eventRepo->shouldReceive('findById')
            ->once()
            ->withArgs(fn($arg) => $arg instanceof EventId && $arg->getValue() === 1)
            ->andReturn($existingEvent);

        $this->eventRepo->shouldReceive('update')->once();

        $this->alertIntervalRepo->shouldReceive('deleteByEventId')->once();
        $this->alertIntervalRepo->shouldReceive('create')->twice();

        $this->tagRepo->shouldReceive('firstOrCreate')->andReturn(new TagId(5));
        $this->eventRepo->shouldReceive('attachTags')->once();

        // Google連携オフ時 → 削除されること
        $this->googleApi->shouldReceive('deleteEvent')
            ->once()
            ->with(Mockery::type(User::class), 's772kpn3nokcs896kbaio1ui1g');

        // GoogleEventId を null に更新
        $this->eventRepo->shouldReceive('updateGoogleEventId')
            ->once()
            ->with(
                Mockery::on(fn($arg) => $arg instanceof EventId && $arg->getValue() === 1),
                null
            );

        // テストデータ（更新内容）
        $data = [
            'id' => 1,
            'user_id' => 123,
            'name' => '更新イベント',
            'event_date' => '2025-06-20 15:00',
            'event_end_date' => '2025-06-20 16:00',
            'memo' => '更新メモ',
            'impression' => '更新感想',
            'alert_intervals' => [
                ['minute_before_event' => 30],
                ['minute_before_event' => 10],
            ],
            'tag_ids' => [3, 4],
            'new_tag_name' => ['仕事']
        ];

        $updatedEvent = $this->eventService->updateEvent($data);

        // Google同期フラグ false → 削除
        $this->eventService->syncWithGoogleCalendar(new User(), $updatedEvent, false);

        $this->assertInstanceOf(Event::class, $updatedEvent);
    }

    public function testDeleteEventAlsoDeletesFromGoogleCalendar()
    {
        $eventId = new EventId(1);
        $googleEventId = new GoogleEventId('s772kpn3nokcs896kbaio1ui1g');

        $event = new Event(
            new EventUserId(123),
            new EventName('削除対象イベント'),
            new EventDate('2025-06-30 10:00'),
            new EventEndDate('2025-06-30 11:00'),
            new EventMemo('削除前のメモ'),            
            new EventImpression('削除前の感想'),
            $googleEventId,
            $eventId
        );

        $this->eventRepo->shouldReceive('findById')
            ->once()
            ->withArgs(fn($arg) => $arg instanceof EventId && $arg->getValue() === 1)
            ->andReturn($event);

        $this->googleApi->shouldReceive('deleteEvent')
            ->once()
            ->with(Mockery::type(User::class), 's772kpn3nokcs896kbaio1ui1g');

        $this->alertIntervalRepo->shouldReceive('deleteByEventId')->once();
        $this->eventRepo->shouldReceive('detachTags')->once();

        $this->eventRepo->shouldReceive('delete')
            ->once()
            ->withArgs(fn($arg) => $arg instanceof EventId && $arg->getValue() === 1);

        // ユーザー情報（トークンなど）
        $user = new User([
            'google_access_token' => 'token',
            'google_refresh_token' => 'refresh',
        ]);

        $this->eventService->deleteFromGoogleCalendar($user, $event);
        $this->eventService->deleteEvent($eventId->getValue());
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
