<?php

namespace App\Domain\Models\AlertInterval;

use App\Domain\Utility\Validator\NumberValidator;

class AlertIntervalId
{
    private int $id;

    public function __construct($id)
    {
        $this->id = NumberValidator::validateNumber($id, [
            'label' => '告知メール間隔ID',
        ]);
    }

    public function getValue(): int
    {
        return $this->id;
    }
}
