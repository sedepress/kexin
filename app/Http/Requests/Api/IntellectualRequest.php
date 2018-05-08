<?php

namespace App\Http\Requests\Api;

class IntellectualRequest extends FormRequest
{
    public function rules()
    {
        switch($this->method()) {
            case 'POST':
                return [
                    'name' => 'required|string',
                    'url' => 'required|string',
                    'image_url' => 'required',
                    'area_id' => 'required|integer',
                ];
                break;
            case 'PATCH':
                return [
                    'status' => 'required|in:yes,no',
                ];
                break;
        }
    }

    public function attributes()
    {
        return [
            'name' => '名称',
            'url' => '网址',
            'image_url' => '图标url',
            'area_id' => '区域id',
        ];
    }
}
