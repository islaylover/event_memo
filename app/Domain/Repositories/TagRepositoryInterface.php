<?php

namespace App\Domain\Repositories;

use App\Domain\Models\Tag\Tag;
use App\Domain\Models\Tag\TagId;
use App\Domain\Models\Tag\TagUserId;

interface TagRepositoryInterface
{
    public function getAllByUserId(TagUserId $userId): array;
    public function firstOrCreate(Tag $tag): TagId;
}
