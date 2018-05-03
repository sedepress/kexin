<?php

namespace App\Http\Requests\Api;

class DeclarationCategoryRequest extends FormRequest
{
    public function rules()
    {
        $rules = [
            'name' => 'required|string',
        ];

        return $rules;
    }
}
