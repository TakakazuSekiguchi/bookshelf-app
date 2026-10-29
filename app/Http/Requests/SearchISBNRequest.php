<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchISBNRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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
