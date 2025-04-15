<?php

namespace App\Domain\Models\Event;

use App\Domain\Utility\Validator\StringValidator;
use InvalidArgumentException;

class EventImpression
{
    private ?string $impression;

    public function __construct($impression)
    {
        if (empty($impression)) {
            $this->impression = null;
        } else {
            $this->impression = StringValidator::validate($impression, [
                'label' => 'イベントの感想',
                'max' => 500
            ]);
        }
    }

    public function getValue(): ?string
    {
        return $this->impression;
    }

    public function __toString(): string
    {
        return $this->impression;
    }
}
