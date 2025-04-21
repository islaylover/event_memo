<?php

namespace App\Domain\Models\Tag;

use App\Domain\Utility\Validator\NumberValidator;

class TagUserId
{
    private int $user_id;

    public function __construct($user_id)
    {
        $this->user_id = NumberValidator::validateNumber($user_id, [
            'label' => 'タグユーザーID',
        ]);
    }

    public function getValue(): int
    {
        return $this->user_id;
    }
}
