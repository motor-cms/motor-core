<?php

namespace Motor\Core\Test\Fixtures\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Motor\Core\Http\Requests\ValidatesAgainstUserClients;

class ValidatesAgainstUserClientsFixtureRequest extends FormRequest
{
    use ValidatesAgainstUserClients;

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'integer', $this->allowedClientIdsRule()],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
