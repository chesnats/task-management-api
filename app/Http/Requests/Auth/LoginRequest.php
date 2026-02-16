<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\Base\ApiRequest;

class LoginRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }

    public function messages()
    {
        return [
            'email.required'    => 'Email is required.',
            'email.email'       => 'Please provide a valid email address.',
            'password.required' => 'Password is required.',
            'password.string'   => 'Password must be a string.',
            'password.min'      => 'Password must be at least 6 characters.',
        ];
    }
}
