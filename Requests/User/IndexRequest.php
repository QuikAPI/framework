<?php
namespace QuikAPI\Requests\User;

use QuikAPI\Requests\FormRequest;

class IndexRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'per_page' => 'sometimes|integer|min:1|max:200',
            'operations' => 'sometimes|array',
            'select' => 'sometimes|array',
            'order_by' => 'sometimes|string',
            'order_type' => 'sometimes|in:asc,desc',
            'group_by' => 'sometimes|string|nullable',
            'return_type' => 'sometimes|in:data,count',
            'with' => 'sometimes|string',
        ];
    }
}
