<?php

namespace Tests\Unit\Domain\Models\Event;

use App\Domain\Models\Event\EventEndDate;
use DateTime;
use InvalidArgumentException;
use Tests\TestCase;

class EventEndDateTest extends TestCase
{
    public function test_正常な日付文字列は_DateTime_オブジェクトになる()
    {
        $dateStr = '2025-04-21 18:30';
        $eventEndDate = new EventEndDate($dateStr);

        $this->assertInstanceOf(DateTime::class, $eventEndDate->getValue());
        $this->assertEquals($dateStr, $eventEndDate->getValue()->format('Y-m-d H:i'));
    }

    public function test_空文字列の場合は_null_になる()
    {
        $eventEndDate = new EventEndDate('');
        
        $this->assertNull($eventEndDate->getValue());
    }

    public function test_不正な形式は例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new EventEndDate('2025/04/21 18:30');
    }
}
