<?php

namespace App\Http\Requests\Api;

class WebsiteCategoryRequest extends FormRequest
{
    public function rules()
    {
        $rules = [
            'name' => 'required|unique:website_categories,name',
            'parent_id' => 'integer',
        ];

        return $rules;
    }
}
