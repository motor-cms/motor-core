<?php

namespace Motor\Core\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class GlobalSearchGetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['required', 'string', 'min:1'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'module' => ['sometimes', 'string'],
        ];
    }
}
