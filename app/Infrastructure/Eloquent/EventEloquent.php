<?php

namespace App\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Infrastructure\Eloquent\TagEloquent;

class EventEloquent extends Model
{
    use HasFactory;

    protected $table = 'events';
    protected $fillable = ['user_id', 'name', 'event_date', 'impression'];


    /**
     * events が持つ alter_intervals のリレーション
     *
     * @return HasMany
     */
    public function alertIntervals(): HasMany
    {
        return $this->hasMany(AlertIntervalEloquent::class, 'event_id', 'id');   
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            TagEloquent::class,
            'event_tag',     // 中間テーブル名
            'event_id',      // 自モデル（EventEloquent）に対応する外部キー名
            'tag_id'         // 相手モデル（TagEloquent）の外部キー名
        );
    }   
}