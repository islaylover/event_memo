<?php

namespace App\Domain\Models\Event;

use App\Domain\Utility\Validator\StringValidator;
use InvalidArgumentException;

class EventName
{
    private string $name;

    public function __construct($name)
    {

        $this->name = StringValidator::validate($name, [
            'label' => 'イベント名',
        ]);
    }

    public function getValue(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
