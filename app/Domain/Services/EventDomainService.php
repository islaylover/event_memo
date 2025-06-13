<?php

namespace App\Domain\Services;

use App\Domain\Models\Event\Event;
use InvalidArgumentException;

class EventDomainService
{
    /**
     * 開始日時と終了日時の整合性チェック
     */
    public static function validateEventDateRange(Event $event): void
    {
        $end = $event->getEventEndDate()->getValue();
        if ($end && $event->getEventDate()->toCarbon()->greaterThan($event->getEventEndDate()->toCarbon())) {
            throw new InvalidArgumentException('終了日時は開始日時と同じかそれ以降にしてください。');
        }
    }

}