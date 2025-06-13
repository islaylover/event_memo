<?php

namespace Tests\Unit\Domain\Models\Event;

use App\Domain\Models\Event\GoogleEventId;
use InvalidArgumentException;
use Tests\TestCase;

class GoogleEventIdTest extends TestCase
{
    public function test_正常なGoogleEventIdが渡されると_valueが取得できる()
    {
        $googleEventId = 's772kpn3nokcs896kbaio1ui1g';  // 実際の形式に合わせた値
        $googleEvent = new GoogleEventId($googleEventId);

        $this->assertEquals($googleEventId, $googleEvent->getValue());
    }

    public function test_空文字列が渡されると_valueがnullになる()
    {
        $googleEventId = '';
        $googleEvent = new GoogleEventId($googleEventId);

        $this->assertNull($googleEvent->getValue());
    }

    public function test_不正なGoogleEventIdが渡されると例外が投げられる()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("無効な Google Event ID: !invalid_id");

        // 不正な Google Event ID の例（英数字以外が含まれている）
        new GoogleEventId('!invalid_id');
    }

    public function test_toStringメソッド()
    {
        $googleEventId = 's772kpn3nokcs896kbaio1ui1g';
        $googleEvent = new GoogleEventId($googleEventId);

        $this->assertEquals($googleEventId, (string) $googleEvent);
    }
}
