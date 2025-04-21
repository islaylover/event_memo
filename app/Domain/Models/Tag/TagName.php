<?php

namespace App\Domain\Models\Tag;

use App\Domain\Utility\Validator\StringValidator;
use InvalidArgumentException;

class TagName
{
    private string $name;

    public function __construct($name)
    {

        $this->name = StringValidator::validate($name, [
            'label' => 'タグ名',
            'max' => 200
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
