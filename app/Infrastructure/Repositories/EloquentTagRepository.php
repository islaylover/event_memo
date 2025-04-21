<?php 

namespace App\Infrastructure\Repositories;

use App\Domain\Models\Tag\Tag;
use App\Domain\Models\Tag\TagId;
use App\Domain\Models\Tag\TagUserId;
use App\Infrastructure\Eloquent\TagEloquent;
use App\Domain\Repositories\TagRepositoryInterface;
use Illuminate\Support\Facades\Log;

class EloquentTagRepository implements TagRepositoryInterface
{
    public function getAllByUserId(TagUserId $userId): array
    {
        return TagEloquent::where('user_id', $userId->getValue())->get()->toArray();
    }

    public function firstOrCreate(Tag $tag): TagId
    {
        $tag = TagEloquent::firstOrCreate(['name' => $tag->getTagName()->getValue(), 'user_id' => $tag->getUserId()->getValue()]);
        return new TagId($tag->id);
    }
}