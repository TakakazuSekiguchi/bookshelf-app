<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchISBNRequest extends FormRequest
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
            'isbn' => ['required', 'regex:/^[0-9]{13}$/'],
        ];
    }

    public function validationData(): array
    {
        return array_merge($this->all(), [
            'isbn' => $this->route('isbn'),
        ]);
    }
}
