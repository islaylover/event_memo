<?php

namespace App\Domain\Utility\Validator;

use DateTime;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class DateValidator
{

    public static function validateDatetime($datetime, string $column): DateTime
    {
        if ($datetime instanceof \DateTimeInterface) {
            // すでに DateTime 型ならフォーマット変換してそのまま使う
            return new DateTime($datetime->format('Y-m-d H:i'));
        }
        $validator = Validator::make(
            ['datetime' => $datetime],
            ['datetime' => ['required', 'date_format:Y-m-d H:i']], // ← datetime-localの形式
            [
                'datetime.required' => "{$column} は必須です。",
                'datetime.date_format' => "{$column} は YYYY-MM-DD HH:MM の形式で入力してください。",
            ]
        );
    
        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first('datetime'));
        }
    
        return new DateTime($datetime);
    }

}
