<?php

namespace Tests\Unit\Domain\Traits;

use App\Domain\Traits\FormatsDate;
use DateTime;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FormatsDateTest extends TestCase
{
    use FormatsDate;

    public function test_有効なDateTimeインスタンスが渡された場合_正しくフォーマットされる()
    {
        $date = new DateTime('2025-04-21 18:30');
        $formattedDate = $this->formatDate($date, 'Y-m-d H:i');

        $this->assertEquals('2025-04-21 18:30', $formattedDate);
    }

    public function test_文字列の日付が渡された場合_正しくフォーマットされる()
    {
        $dateStr = '2025-04-21 18:30';
        $formattedDate = $this->formatDate($dateStr, 'Y-m-d H:i');

        $this->assertEquals('2025-04-21 18:30', $formattedDate);
    }

    public function test_空の値が渡された場合_空文字列を返す()
    {
        $formattedDate = $this->formatDate('');

        $this->assertEquals('', $formattedDate);
    }

    public function test_無効な日付が渡された場合_例外をスローする()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid date format');

        $this->formatDate('invalid-date');
    }

    public function test_不正な日付形式が渡された場合_例外をスローする()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->formatDate('2025/99/99 99:99'); // 存在しない日付
    }
}
