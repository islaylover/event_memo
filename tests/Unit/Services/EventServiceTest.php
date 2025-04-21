<?php

namespace Tests\Unit\Services;

use App\Services\EventService;
use App\Domain\Models\Event\Event;
use App\Domain\Models\Event\EventId;
use App\Domain\Models\Event\EventUserId;
use App\Domain\Models\Event\EventName;
use App\Domain\Models\Event\EventDate;
use App\Domain\Models\Event\EventImpression;

use App\Domain\Repositories\EventRepositoryInterface;
use App\Domain\Repositories\AlertIntervalRepositoryInterface;
use App\Domain\Repositories\TagRepositoryInterface;
use App\Infrastructure\Eloquent\EventEloquent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Tests\TestCase;
use Mockery;

class EventServiceTest extends TestCase
{
    private $eventRepo;
    private $alertRepo;
    private $tagRepo;
    private $eventService;

    protected function setUp(): void
    {
        parent::setUp();

        //Mockery::close();
        $this->eventRepo = Mockery::mock(EventRepositoryInterface::class);
        $this->alertRepo = Mockery::mock(AlertIntervalRepositoryInterface::class);
        $this->tagRepo   = Mockery::mock(TagRepositoryInterface::class);

        Auth::shouldReceive('id')->andReturn(123);

        $this->eventService = new EventService(
            $this->eventRepo,
            $this->alertRepo,
            $this->tagRepo
        );
    }

    public function test_create_event_成功する()
    {
        // Arrange: 適当なイベントデータ
        $data = [
            'user_id' => 1,
            'name' => 'テストイベント',
            'event_date' => '2025-04-22 12:00',
            'impression' => 'これはテストです',
            'alert_intervals' => [['minute_before_event' => 10]],
            'tag_ids' => [1, 2],
            'new_tag_name' => ['テストタグ']
        ];

        DB::shouldReceive('beginTransaction');
        DB::shouldReceive('commit');
        DB::shouldReceive('rollBack');

        // EventRepository::create が EventId を返すようにモック
        $this->eventRepo->shouldReceive('create')->once()->andReturnUsing(function () {
            return new \App\Domain\Models\Event\EventId(999);
        });

        // AlertIntervalRepository::create 呼ばれることを想定
        $this->alertRepo->shouldReceive('create')->once();

        // TagRepository::firstOrCreate 呼ばれることを想定
        $this->tagRepo->shouldReceive('firstOrCreate')->andReturn(
            new \App\Domain\Models\Tag\TagId(1)
        );

        // EventRepository::attachTags 呼ばれることを想定
        $this->eventRepo->shouldReceive('attachTags')->once();

        // Act + Assert（例外が投げられなければ成功）
        $this->eventService->createEvent($data);

        $this->assertTrue(true);
    }

    public function test_update_event_成功する()
    {
        // Arrange
        $data = [
            'id' => 999,
            'user_id' => 123, // Auth::id() に合わせる
            'name' => '更新イベント',
            'event_date' => '2025-04-23 15:00',
            'impression' => '更新内容です',
            'alert_intervals' => [['minute_before_event' => 15]],
            'tag_ids' => [1],
            'new_tag_name' => ['新しいタグ']
        ];
        // 「イベントが存在して、ユーザーIDも一致している」ことを前提とした EventEloquent を事前登録
        EventEloquent::unguard();
        $mockEloquent = new \App\Infrastructure\Eloquent\EventEloquent([
            'id' => 999,
            'user_id' => 123,
        ]);

        DB::shouldReceive('beginTransaction');
        DB::shouldReceive('commit');
        DB::shouldReceive('rollBack');

        // モック定義
        $this->eventRepo->shouldReceive('findById')->once()->andReturn(
            new \App\Domain\Models\Event\Event(
                new \App\Domain\Models\Event\EventUserId(123),
                new \App\Domain\Models\Event\EventName('旧イベント名'),
                new \App\Domain\Models\Event\EventDate('2025-04-20 10:00'),
                new \App\Domain\Models\Event\EventImpression('旧インプレッション'),
                new \App\Domain\Models\Event\EventId(999)
            )
        );

        $this->eventRepo->shouldReceive('update')->once();
        
        $this->alertRepo->shouldReceive('deleteByEventId')->once();
        
        $this->alertRepo->shouldReceive('create')->once();
        
        $this->tagRepo->shouldReceive('firstOrCreate')->once()->andReturn(
            new \App\Domain\Models\Tag\TagId(1)
        );
        $this->eventRepo->shouldReceive('attachTags')->once();
    
        // Act
        $this->eventService->updateEvent($data);
    
        // Assert
        $this->assertTrue(true); // 例外が出なければ成功
    }
    
    public function test_delete_event_成功する()
    {
    
        // DBトランザクションのモック
        DB::shouldReceive('beginTransaction');
        DB::shouldReceive('commit');
        DB::shouldReceive('rollBack');
    
        $this->eventRepo->shouldReceive('findById')->once()->andReturn(
            new \App\Domain\Models\Event\Event(
                new \App\Domain\Models\Event\EventUserId(123),
                new \App\Domain\Models\Event\EventName('旧イベント名'),
                new \App\Domain\Models\Event\EventDate('2025-04-20 10:00'),
                new \App\Domain\Models\Event\EventImpression('旧インプレッション'),
                new \App\Domain\Models\Event\EventId(999)
            )
        );
    
        // 各リポジトリ呼び出しをモック
        $this->alertRepo->shouldReceive('deleteByEventId')->once();
        $this->eventRepo->shouldReceive('detachTags')->once();
        $this->eventRepo->shouldReceive('delete')->once()->andReturn(true);
    
        // Act
        $result = $this->eventService->deleteEvent(123);
    
        // Assert
        $this->assertTrue($result);
    }    

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
