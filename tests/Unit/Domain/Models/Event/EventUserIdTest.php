<?php

namespace Tests\Unit\Domain\Models\Event;

use App\Domain\Models\Event\EventUserId;
use InvalidArgumentException;
use Tests\TestCase;

class EventUserIdTest extends TestCase
{
    public function test_正常なユーザーIDでインスタンス生成できる()
    {
        $userId = new EventUserId(123);
        $this->assertEquals(123, $userId->getValue());
    }

    public function test_空文字は例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new EventUserId('');
    }

    public function test_nullは例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new EventUserId(null);
    }

    public function test_文字列は例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new EventUserId('abc');
    }

    public function test_配列は例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new EventUserId([1, 2, 3]);
    }
}
