<?php

namespace App\Domain\Utility\Validator;

use InvalidArgumentException;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Log;

class StringValidator
{
    /**
     * 指定された文字列を検証します。
     *
     * @param mixed   $text 文字列
     * @param array   $options [validation rule]    
     * @return String 検証済みの文字列オブジェクト
     * @throws InvalidArgumentException 無効な文字列形式の場合
     */
    public static function validate($text, array $options): string
    {
        $label = $options['label'] ?? 'テキスト';
        $max   = $options['max'] ?? null;
        $kana  = $options['kana'] ?? false;
    
        $rules = ['required', 'string'];
        $messages = [
            'text.required' => "{$label} は必須です。",
            'text.string'   => "{$label} は文字列である必要があります。",
        ];
    
        if (!is_null($max)) {
            $rules[] = "max:{$max}";
            $messages['text.max'] = "{$label} は {$max} 文字以内で入力してください。";
        }
    
        if ($kana) {
            $rules[] = 'regex:/^[ぁ-ゟ゠-ヿー]+$/u';
            $messages['text.regex'] = "{$label} はひらがなまたはカタカナで入力してください。";
        }

        Log::info("StringValidator error message:". var_export($messages, true));
        $validator = Validator::make(
            ['text' => $text], 
            ['text' => $rules], 
            $messages
        );
    
        if ($validator->fails()) {
            Log::info("StringValidator error");
            Log::info(var_export($validator->errors()->first('text'), true));
            throw new InvalidArgumentException($validator->errors()->first('text'));
        }
    
        return $text;
    }
}