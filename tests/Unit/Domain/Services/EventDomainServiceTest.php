<?php

namespace Tests\Unit\Domain\Services;

use App\Domain\Services\EventDomainService;
use App\Domain\Models\Event\Event;
use App\Domain\Models\Event\EventUserId;
use App\Domain\Models\Event\EventName;
use App\Domain\Models\Event\EventDate;
use App\Domain\Models\Event\EventEndDate;
use App\Domain\Models\Event\EventMemo;
use App\Domain\Models\Event\EventImpression;
use App\Domain\Models\Event\GoogleEventId;
use InvalidArgumentException;
use Tests\TestCase;

class EventDomainServiceTest extends TestCase
{
    public function test_正常な日付範囲では例外が発生しない()
    {
        $event = new Event(
            new EventUserId(1),
            new EventName('テストイベント'),
            new EventDate('2025-06-13 10:00'),
            new EventEndDate('2025-06-13 11:00'),
            new EventMemo('メモ'),
            new EventImpression('印象'),
            new GoogleEventId(null)
        );

        EventDomainService::validateEventDateRange($event);
        $this->assertTrue(true);
    }

    public function test_開始日時が終了日時より後なら例外が発生する()
    {
        $this->expectException(InvalidArgumentException::class);

        $event = new Event(
            new EventUserId(1),
            new EventName('テストイベント'),
            new EventDate('2025-06-13 15:00'),
            new EventEndDate('2025-06-13 14:00'),
            new EventMemo('メモ'),
            new EventImpression('印象'),
            new GoogleEventId(null)
        );

        EventDomainService::validateEventDateRange($event);
    }
}