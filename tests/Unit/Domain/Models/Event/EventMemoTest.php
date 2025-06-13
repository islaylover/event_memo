<?php

namespace Tests\Unit\Domain\Models\Event;


use App\Domain\Models\Event\EventMemo;
use InvalidArgumentException;
use Tests\TestCase;

class EventMemoTest extends TestCase
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

    public function test_正常なイベントメモはインスタンス生成できる()
    {
        $name = new EventMemo('このメンバーだと三次会まで強制参加かな・・・？');
        $this->assertEquals('このメンバーだと三次会まで強制参加かな・・・？', $name->getValue());
    }

    public function test_50文字以内はインスタンス生成できる()
    {
        $longMemo = str_repeat('あ', 50);
        $this->assertTrue(true);
        new EventMemo($longMemo);
    }

    public function test_50文字を超えると例外を投げる()
    {
        $longMemo = str_repeat('あ', 51);
        $this->expectException(InvalidArgumentException::class);
        new EventMemo($longMemo);
    }

    public function test_空文字はnullとして扱われる()
    {
        $memo = new EventMemo('');
        $this->assertNull($memo->getValue());
    }

    public function test_nullはそのままnullとして扱われる()
    {
        $memo = new EventMemo(null);
        $this->assertNull($memo->getValue());
    }
}
