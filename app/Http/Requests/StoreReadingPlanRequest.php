<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReadingPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'book_id' => ['required', 'exists:books,id'],
            'target_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'book_id.required' => 'タイトルは必須です。',
            'book_id.*.exists' => '指定されたジャンルが存在しません。',
            'target_date.required' => '期日は必須です。',
            'target_date.date' => '正しい日付を入力してください。',
            'target_date.after_or_equal' => '今日以降の日付を入力してください。',
        ];
    }
}
