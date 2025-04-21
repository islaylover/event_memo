<?php

namespace Tests\Unit\Domain\Models\Tag;


use App\Domain\Models\Tag\TagName;
use InvalidArgumentException;
use Tests\TestCase;

class TagNameTest extends TestCase
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
        $name = new TagName('健康');
        $this->assertEquals('健康', $name->getValue());
    }
    
    public function test_空文字列は例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new TagName('');
    }
    
    public function test_200文字を超えると例外を投げる()
    {
        $longName = str_repeat('あ', 201);
        $this->expectException(InvalidArgumentException::class);
        new TagName($longName);
    }
    
}
