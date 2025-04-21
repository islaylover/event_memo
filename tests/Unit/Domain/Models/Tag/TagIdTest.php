<?php

namespace Tests\Unit\Domain\Models\Tag;

use App\Domain\Models\Tag\TagId;
use InvalidArgumentException;
use Tests\TestCase;

class TagIdTest extends TestCase
{
    public function test_正常なTagIDでインスタンス生成できる()
    {
        $tagId = new TagId(123);
        $this->assertEquals(123, $tagId->getValue());
    }

    public function test_空文字は例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new TagId('');
    }

    public function test_nullは例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new TagId(null);
    }

    public function test_文字列は例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new TagId('abc');
    }

    public function test_配列は例外を投げる()
    {
        $this->expectException(InvalidArgumentException::class);
        new TagId([1, 2, 3]);
    }
}
