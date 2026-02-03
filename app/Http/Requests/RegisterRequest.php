<?php

namespace App\Http\Requests;

use App\Http\Requests\Base\ApiRequest;

class RegisterRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'     => ['required', 'string', 'max:255', 'unique:users,name'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'confirmed', 'min:6'],
        ];
    }

    public function messages()
    {
        return [
            'name.required'        => 'Name is required.',
            'name.string'          => 'Name must be a string.',
            'name.max'             => 'Name may not be greater than 255 characters.',
            'name.unique'          => 'Name has already been taken.',
            'email.required'       => 'Email is required.',
            'email.email'          => 'Please provide a valid email address.',
            'email.unique'         => 'Email has already been taken.',
            'password.required'    => 'Password is required.',
            'password.string'      => 'Password must be a string.',
            'password.min'         => 'Password must be at least 6 characters.',
            'password.confirmed'   => 'Password confirmation does not match.',
        ];
    }
}
