<?php

namespace App\Infrastructure\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertIntervalEloquent extends Model
{
    use HasFactory;

    protected $table = 'alert_intervals';
    protected $fillable = ['event_id', 'minute_before_event'];


   /**
     * alert_intervals が所属する events のリレーション
     *
     * @return BelongsTo
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(EventEloquent::class, 'event_id', 'id');
    }
}