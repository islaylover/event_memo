<?php

namespace App\Domain\Models\Tag;

use App\Domain\Models\Tag\TagId;
use App\Domain\Models\Tag\TagName;

class Tag
{
    private ?TagId $id = null;
    private TagName $name;

    public function __construct(
        TagName $name,
        ?TagId $id = null
    ) {
        $this->id = $id;
        $this->name = $name;
    }

    public function getId(): ?TagId
    {
        return $this->id;
    }

    public function getTagName(): TagName
    {
        return $this->name;
    }
}
