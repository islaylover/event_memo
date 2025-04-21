<?php

namespace Tests\Unit\Domain\Models\AlertInterval;

use App\Domain\Models\AlertInterval\AlertIntervalId;
use DateTime;
use InvalidArgumentException;
use Tests\TestCase;

class AlertIntervalIdTest extends TestCase
{
    public function test_正常なAlertIntervalDでインスタンス生成できる()
    {
        $alertIntervalId = new AlertIntervalId(123);
        $this->assertEquals(123, $alertIntervalId->getValue());
    }

    public function test_空文字は例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new AlertIntervalId('');
    }

    public function test_nullは例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new AlertIntervalId(null);
    }

    public function test_文字列は例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new AlertIntervalId('abc');
    }

    public function test_配列は例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new AlertIntervalId([1, 2, 3]);
    }
}
