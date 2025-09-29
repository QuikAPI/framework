<?php
namespace QuikAPI\Requests\User;

use QuikAPI\Requests\FormRequest;

class UpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:120',
            'email' => 'sometimes|email',
            'password' => 'sometimes|string|min:6',
        ];
    }
}
