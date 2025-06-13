<?php

namespace App\Domain\Models\Tag;

use App\Domain\Utility\Validator\NumberValidator;

class TagUserId
{
    private int $userId;

    public function __construct($userId)
    {
        $this->userId = NumberValidator::validateNumber($userId, [
            'label' => 'タグユーザーID',
        ]);
    }

    public function getValue(): int
    {
        return $this->userId;
    }
}
