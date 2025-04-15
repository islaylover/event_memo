<?php

namespace App\Domain\Models\Event;

use App\Domain\Utility\Validator\NumberValidator;

class EventId
{
    private int $id;

    public function __construct($id)
    {
        $this->id = NumberValidator::validateNumber($id, [
            'label' => 'イベントID',
        ]);
    }

    public function getValue(): int
    {
        return $this->id;
    }
}
