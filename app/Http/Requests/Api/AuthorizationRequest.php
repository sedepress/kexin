<?php

namespace App\Http\Requests\Api;

class AuthorizationRequest extends FormRequest
{
    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ];
    }
}
