<?php

namespace App\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Infrastructure\Eloquent\EventEloquent;

class TagEloquent extends Model
{
    use HasFactory;

    protected $table = 'tags';
    protected $fillable = ['user_id', 'name'];


    public function events(): BelongsToMany
    {
        return $this->belongsToMany(
            EventEloquent::class,
            'event_tag',
            'tag_id',
            'event_id'
        )->withTimestamps();
    }
}