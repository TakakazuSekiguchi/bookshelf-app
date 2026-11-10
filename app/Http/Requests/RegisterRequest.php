<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required','confirmed'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'お名前を入力してください。',
            'name.max' => 'お名前は255文字以内で入力してください。',
            'email.required' => 'メールアドレスを入力してください。',
            'email.email' => 'メールアドレスはメール形式で入力してください。',
            'email.unique' => 'そのメールアドレスは既に使用されています。',
            'password.required' => 'パスワードを入力してください。',
            'password.confirmed' => 'パスワードと一致しません。',
        ];
    }
}
