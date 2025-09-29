<?php
namespace QuikAPI\Requests\User;

use QuikAPI\Requests\FormRequest;

class StoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:120',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ];
    }
}
