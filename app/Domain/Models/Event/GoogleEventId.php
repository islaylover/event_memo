<?php

namespace App\Domain\Models\Event;

use App\Domain\Utility\Validator\StringValidator;
use InvalidArgumentException;

class GoogleEventId
{
    private ?string $googleEventId;

    public function __construct($googleEventId)
    {
        // 空文字の処理
        if (empty($googleEventId)) {
            $this->googleEventId = null;
            return;
        }

        // Google Event IDは英数字のみを許可（ここでバリデーションを追加）
        if (!preg_match('/^[a-zA-Z0-9]+$/', $googleEventId)) {
            throw new InvalidArgumentException("無効な Google Event ID: " . $googleEventId);
        }

        // バリデーションとパース
        $this->googleEventId = StringValidator::validate($googleEventId, [
            'label' => 'Google Event Id',
            'max' => 255,
        ]);
    }

    public function getValue(): ?string
    {
        return $this->googleEventId;
    }

    public function __toString(): string
    {
        return (string) $this->googleEventId;
    }
}