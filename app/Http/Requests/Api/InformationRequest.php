<?php

namespace App\Http\Requests\Api;

class InformationRequest extends FormRequest
{
    public function rules()
    {
        switch($this->method()) {
            case 'POST':
                return [
                    'title' => 'required|string',
                    'content' => 'required|string',
                    'publisher' => 'required|string',
                    'image_url' => 'required',
                ];
                break;
            case 'PUT':
                return [
                    'status' => 'required|in:yes,no',
                ];
                break;
            case 'PATCH':
                return [
                    'status' => 'required|in:up,down',
                ];
                break;
        }
    }

    public function attributes()
    {
        return [
            'title' => '标题',
            'content' => '内容',
            'publisher' => '发布人',
            'image_url' => '图片',
        ];
    }
}
