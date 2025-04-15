<?php

namespace App\Domain\Utility\Validator;

use InvalidArgumentException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class DigitValidator
{

     /**
     * 指定された値の桁数を検証します。
     *
     * @param  mixed $number 数値
     * @param  string $column カラム名
     * @param  int $length 桁数
     * @return int   検証済みの数値
     * @throws InvalidArgumentException 無効な桁数の場合
     */
    public static function validateDigits($number, string $column, int $length): int
    {
        $validator = Validator::make(
            ['number' => $number],
            ['number' => ['required', 'numeric', 'digits:'.$length]],
            [
                'number.required' => "{$column} は必須です。",
                'number.numeric'  => "{$column} は整数である必要があります。",
                'number.digits'   => "{$column} は {$length} 桁でなければなりません。",
            ]
        );

        if ($validator->fails()) {
            Log::info("validateDigits validation error");
            throw new InvalidArgumentException($validator->errors()->first('number'));
        }

        return $number;
    }

    /**
     * 指定された値の桁数が指定された範囲内かを検証します。
     *
     * @param  mixed $number 数値
     * @param  string $column カラム名
     * @param  int $minLength 最小桁数
     * @param  int $maxLength 最大桁数
     * @return int   検証済みの数値
     * @throws InvalidArgumentException 無効な桁数の場合
     */
    public static function validateDigitsBetween($number, string $column, int $minLength, int $maxLength): string
    {
        $validator = Validator::make(
            ['number' => $number],
            ['number' => ['required', 'regex:/^\d{10,11}$/']],
            [
                'number.required' => "{$column} は必須です。",
                'number.regex'  => "{$column} は数値である必要があります。",
                'number.digits_between' => "{$column} は {$minLength} 桁以上、{$maxLength} 桁以下でなければなりません。",
            ]
        );

        if ($validator->fails()) {
            throw new InvalidArgumentException($validator->errors()->first('number'));
        }

        return (string) $number;
    }
}

