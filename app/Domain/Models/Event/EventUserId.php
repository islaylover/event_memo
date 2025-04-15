<?php

namespace App\Domain\Models\Event;

use App\Domain\Utility\Validator\NumberValidator;

class EventUserId
{
    private int $user_id;

    public function __construct($user_id)
    {
        $this->user_id = NumberValidator::validateNumber($user_id, [
            'label' => 'イベントユーザーID',
        ]);
    }

    public function getValue(): int
    {
        return $this->user_id;
    }
}
