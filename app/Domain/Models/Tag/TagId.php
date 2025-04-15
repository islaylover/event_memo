<?php

namespace App\Domain\Models\Tag;

use App\Domain\Utility\Validator\NumberValidator;
use InvalidArgumentException;

class TagId
{
    private int $id;

    public function __construct($id)
    {
        $this->id = NumberValidator::validateNumber($id, [
            'label' => 'タグID',
        ]);
    }

    public function getValue(): int
    {
        return $this->id;
    }
}
