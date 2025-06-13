<?php

namespace App\Domain\Models\Event;

use App\Domain\Utility\Validator\NumberValidator;

class EventUserId
{
    private int $userId;

    public function __construct($userId)
    {
        $this->userId = NumberValidator::validateNumber($userId, [
            'label' => 'イベントユーザーID',
        ]);
    }

    public function getValue(): int
    {
        return $this->userId;
    }
}
