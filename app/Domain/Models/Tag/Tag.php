<?php

namespace App\Domain\Models\Tag;

use App\Domain\Models\Tag\TagId;
use App\Domain\Models\Tag\TagUserId;
use App\Domain\Models\Tag\TagName;

class Tag
{
    private ?TagId $id = null;
    private TagUserId $userId;
    private TagName $name;

    public function __construct(
        TagUserId $userId,
        TagName $name,
        ?TagId $id = null
    ) {
        $this->id = $id;
        $this->userId = $userId;
        $this->name = $name;
    }

    public function getId(): ?TagId
    {
        return $this->id;
    }

    public function getUserId(): ?TagUserId
    {
        return $this->userId;
    }

    public function getTagName(): TagName
    {
        return $this->name;
    }
}
