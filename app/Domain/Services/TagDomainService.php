<?php

namespace App\Domain\Services;

use App\Domain\Models\Tag\Tag;

class TagDomainService
{
    public static function removeDuplicateIds(array $tagIds): array
    {
        return array_values(array_unique($tagIds));
    }

    public static function removeDuplicateEntities(array $tagEntities): array
    {
        $seen = [];
        $result = [];

        foreach ($tagEntities as $tag) {
            $key = (string)$tag->getTagName(); // TagName::__toString() で比較
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $result[] = $tag;
            }
        }
        return $result;
    }
}
