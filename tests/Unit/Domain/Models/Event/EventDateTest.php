<?php

namespace Tests\Unit\Domain\Models\Event;

use App\Domain\Models\Event\EventDate;
use DateTime;
use InvalidArgumentException;
use Tests\TestCase;

class EventDateTest extends TestCase
{
    public function test_正常な日付文字列は_DateTime_オブジェクトになる()
    {
        $dateStr = '2025-04-21 18:30';
        $eventDate = new EventDate($dateStr);

        $this->assertInstanceOf(DateTime::class, $eventDate->getValue());
        $this->assertEquals($dateStr, $eventDate->getValue()->format('Y-m-d H:i'));
    }

    public function test_空文字列は例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new EventDate('');
    }

    public function test_不正な形式は例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new EventDate('2025/04/21 18:30');
    }
}
