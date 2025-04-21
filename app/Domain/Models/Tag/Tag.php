<?php

namespace App\Domain\Models\Tag;

use App\Domain\Models\Tag\TagId;
use App\Domain\Models\Tag\TagUserId;
use App\Domain\Models\Tag\TagName;

class Tag
{
    private ?TagId $id = null;
    private TagUserId $user_id;
    private TagName $name;

    public function __construct(
        TagUserId $user_id,
        TagName $name,
        ?TagId $id = null
    ) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->name = $name;
    }

    public function getId(): ?TagId
    {
        return $this->id;
    }

    public function getUserId(): ?TagUserId
    {
        return $this->user_id;
    }

    public function getTagName(): TagName
    {
        return $this->name;
    }
}
