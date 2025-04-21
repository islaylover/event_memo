<?php

namespace Tests\Unit\Domain\Models\Event;

use App\Domain\Models\Event\EventId;
use InvalidArgumentException;
use Tests\TestCase;

class EventIdTest extends TestCase
{
    public function test_正常なEventIDでインスタンス生成できる()
    {
        $eventId = new EventId(123);
        $this->assertEquals(123, $eventId->getValue());
    }

    public function test_空文字は例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new EventId('');
    }

    public function test_nullは例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new EventId(null);
    }

    public function test_文字列は例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new EventId('abc');
    }

    public function test_配列は例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new EventId([1, 2, 3]);
    }
}
