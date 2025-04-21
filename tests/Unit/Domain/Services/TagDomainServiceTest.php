<?php

namespace Tests\Unit\Domain\Services;

use App\Domain\Models\Tag\Tag;
use App\Domain\Models\Tag\TagName;
use App\Domain\Models\Tag\TagUserId;
use App\Domain\Services\TagDomainService;
use Tests\TestCase;

class TagDomainServiceTest extends TestCase
{
    public function test_重複したTagIDは除去される()
    {
        $raw = [1, 2, 1, 3];
        $result = TagDomainService::removeDuplicateIds($raw);

        $this->assertEquals([1, 2, 3], $result);
    }

    public function test_重複したTagEntityは名前で除去される()
    {
        $userId = new TagUserId(1);
        $tags = [
            new Tag($userId, new TagName('趣味')),
            new Tag($userId, new TagName('健康')),
            new Tag($userId, new TagName('趣味')), // duplicate
        ];

        $result = TagDomainService::removeDuplicateEntities($tags);

        $this->assertCount(2, $result);
        $this->assertEquals('趣味', $result[0]->getTagName()->getValue());
        $this->assertEquals('健康', $result[1]->getTagName()->getValue());
    }
}
