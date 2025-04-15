<?php

namespace App\Domain\Utility\Validator;

use DateTime;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;

class DateValidator
{

    public static function validateDatetime($datetime, string $column): DateTime
    {
        $validator = Validator::make(
            ['datetime' => $datetime],
            ['datetime' => ['required', 'date_format:Y-m-d H:i']], // ← datetime-localの形式
            [
                'datetime.required' => "{$column} は必須です。",
                'datetime.date_format' => "{$column} は YYYY-MM-DDTHH:MM の形式で入力してください。",
            ]
        );
    
        if ($validator->fails()) {
            Log::info("Datetime validation error");
            throw new InvalidArgumentException($validator->errors()->first('datetime'));
        }
    
        return new DateTime($datetime);
    }

    public static function validateYmd($date, string $column): DateTime
    {
        // null許容の場合はnullableルールを適用
        $rules = ['date' => ['date_format:Y-m-d']];
        $rules['date'][] = 'required';  // 必須チェック
    
        $validator = Validator::make(
            ['date' => $date],
            $rules,
            [
                'date.required'      => "{$column} は必須です。",
                'date.date_format'   => "{$column} は YYYY-MM-DD の形式で入力してください。",
            ]
        );
    
        if ($validator->fails()) {
            Log::info("Date validation error");
            throw new InvalidArgumentException($validator->errors()->first('date'));
        }
    
        // バリデーションが成功した場合、DateTimeオブジェクトを返す
        if ($date === null && $nullable) {
            return null;  // null を返す
        }
    
        return new DateTime($date);  // DateTimeオブジェクトを返す
    }

    /**
     * 指定された時分秒（H:i:s）をバリデーション
     */
    public static function validateTime(?string $time, string $column): ?DateTime
    {
        $validator = Validator::make(
            ['time' => $time],
            ['time' => ['required', 'date_format:H:i:s']],
            [
                'time.required'    => "{$column} は必須です。",
                'time.date_format' => "{$column} は HH:MM:SS の形式で入力してください。",
            ]
        );

        if ($validator->fails()) {
            Log::info("Time validation error");
            throw new InvalidArgumentException($validator->errors()->first('time'));
        }

        return $time ? new DateTime($time) : null;
    }
}
