<?php

namespace Tests\Unit\Domain\Services;

use App\Domain\Services\AlertIntervalDomainService;
use Tests\TestCase;

class AlertIntervalDomainServiceTest extends TestCase
{
    public function test_重複したアラート間隔は除去される()
    {
        $raw = [
            ['minute_before_event' => 10],
            ['minute_before_event' => 5],
            ['minute_before_event' => 10],
        ];

        $result = AlertIntervalDomainService::removeDuplicates($raw);

        $this->assertCount(2, $result);
        $this->assertEquals([10, 5], array_column($result, 'minute_before_event'));
    }

    public function test_空配列でも例外が起きない()
    {
        $result = AlertIntervalDomainService::removeDuplicates([]);
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
