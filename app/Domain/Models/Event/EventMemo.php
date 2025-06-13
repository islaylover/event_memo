<?php

namespace App\Domain\Models\Event;

use App\Domain\Utility\Validator\StringValidator;
use InvalidArgumentException;

class EventMemo
{
    private ?string $memo;

    public function __construct($memo)
    {
        if (empty($memo)) {
            $this->memo = null;
        } else {
            $this->memo = StringValidator::validate($memo, [
                'label' => 'イベントのメモ',
                'max' => 50
            ]);
        }
    }

    public function getValue(): ?string
    {
        return $this->memo;
    }

    public function __toString(): string
    {
        return $this->memo;
    }
}
