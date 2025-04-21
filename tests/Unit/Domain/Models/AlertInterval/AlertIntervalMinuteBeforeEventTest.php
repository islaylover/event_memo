<?php

namespace Tests\Unit\Domain\Models\AlertInterval;

use App\Domain\Models\AlertInterval\AlertIntervalMinuteBeforeEvent;
use DateTime;
use InvalidArgumentException;
use Tests\TestCase;

class AlertIntervalMinuteBeforeEventTest extends TestCase
{
    public function test_正常なAlertIntervalMinuteBeforeEventでインスタンス生成できる()
    {
        $alertIntervalId = new AlertIntervalMinuteBeforeEvent(10);
        $this->assertEquals(10, $alertIntervalId->getValue());
    }

    public function test_空文字は例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new AlertIntervalMinuteBeforeEvent('');
    }

    public function test_nullは例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new AlertIntervalMinuteBeforeEvent(null);
    }

    public function test_文字列は例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new AlertIntervalMinuteBeforeEvent('abc');
    }

    public function test_配列は例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new AlertIntervalMinuteBeforeEvent([1, 2, 3]);
    }
}
