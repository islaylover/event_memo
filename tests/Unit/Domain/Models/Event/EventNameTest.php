<?php

namespace Tests\Unit\Domain\Models\Event;


use App\Domain\Models\Event\EventName;
use InvalidArgumentException;
use Tests\TestCase;

class EventNameTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_example()
    {
        $this->assertTrue(true);
    }

    public function test_正常なイベント名はインスタンス生成できる()
    {
        $name = new EventName('週末ハイキング');
        $this->assertEquals('週末ハイキング', $name->getValue());
    }
    
    public function test_空文字列は例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new EventName('');
    }

    public function test_50文字以内はインスタンス生成できる()
    {
        $longName = str_repeat('あ', 50);
        $this->assertTrue(true);
        new EventName($longName);
    }

    public function test_50文字を超えると例外を投げる()
    {
        $longName = str_repeat('あ', 51);
        $this->expectException(InvalidArgumentException::class);
        new EventName($longName);
    }
    
}
