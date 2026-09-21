<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:100'],
            'isbn' => ['required', 'string', 'size:13', Rule::unique('books', 'isbn')->ignore($this->book)],
            'published_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'genres' => ['required', 'array'],
            'genres.*' => ['integer', 'exists:genres,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'ユーザーIDは必須です。',
            'user_id.integer' => 'ユーザーIDは整数で指定してください。',
            'user_id.exists' => '指定されたユーザーIDが存在しません。',

            'title.required' => 'タイトルは必須です。',
            'title.string' => 'タイトルは文字列で入力してください。',
            'title.max' => 'タイトルは255文字以内で入力してください。',

            'author.required' => '著者名は必須です。',
            'author.string' => '著者名は文字列で入力してください。',
            'author.max' => '著者名は100文字以内で入力してください。',

            'isbn.required' => 'ISBN-13は必須です。',
            'isbn.size' => 'ISBN-13は13文字で入力してください。',
            'isbn.unique' => 'このISBN-13は既に登録されています。',

            'published_date.required' => '出版日は必須です。',
            'published_date.date' => '正しい日付を入力してください。',

            'description.string' => '説明は文字列で入力してください。',

            'image_url.url' => '正しいURLを入力してください。',
            'image_url.max' => '画像URLは2048文字以内で入力してください。',

            'genres.required' => 'ジャンルは必須です。',
            'genres.array' => 'ジャンルは配列で指定してください。',
            'genres.*.integer' => 'ジャンルIDは整数で指定してください。',
            'genres.*.exists' => '指定されたジャンルが存在しません。',
        ];
    }
}
