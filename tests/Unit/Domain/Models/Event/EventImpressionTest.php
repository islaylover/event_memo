<?php

namespace Tests\Unit\Domain\Models\Event;


use App\Domain\Models\Event\EventImpression;
use InvalidArgumentException;
use Tests\TestCase;

class EventImpressionTest extends TestCase
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

    public function test_正常なイベント感想はインスタンス生成できる()
    {
        $name = new EventImpression('ずっと楽しみにしていたデートだったけど、天気も悪く、選んだ日付と場所が最悪でいく先々全て超混雑。仲良くなるどころか喧嘩してしまった。今度会ったら謝らなきゃ。');
        $this->assertEquals('ずっと楽しみにしていたデートだったけど、天気も悪く、選んだ日付と場所が最悪でいく先々全て超混雑。仲良くなるどころか喧嘩してしまった。今度会ったら謝らなきゃ。', $name->getValue());
    }

    public function test_500文字以内はインスタンス生成できる()
    {
        $longName = str_repeat('あ', 500);
        $this->assertTrue(true);
        new EventImpression($longName);
    }

    public function test_500文字を超えると例外を投げる()
    {
        $longName = str_repeat('あ', 501);
        $this->expectException(InvalidArgumentException::class);
        new EventImpression($longName);
    }

    public function test_空文字はnullとして扱われる()
    {
        $memo = new EventImpression('');
        $this->assertNull($memo->getValue());
    }

    public function test_nullはそのままnullとして扱われる()
    {
        $memo = new EventImpression(null);
        $this->assertNull($memo->getValue());
    }
}
