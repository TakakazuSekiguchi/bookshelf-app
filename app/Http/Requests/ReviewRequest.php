<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => '評価は必須です。',
            'rating.integer' => '評価は整数で指定してください。',
            'rating.between' => '評価は1〜5の範囲で指定してください。',
            'comment.required' => 'コメントは必須です。',
            'comment.string' => 'コメントは文字列で指定してください。',
        ];
    }
}