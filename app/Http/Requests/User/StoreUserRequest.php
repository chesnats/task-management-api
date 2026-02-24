<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Base\ApiRequest;

class StoreUserRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role'     => ['sometimes', 'in:user,team_leader'],
            'team_id'  => ['sometimes', 'required_if:role,team_leader', 'nullable', 'exists:teams,id'],
            'avatar'   => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function messages()
    {
        return [
            'name.required'     => 'Name is required.',
            'email.required'    => 'Email is required.',
            'email.email'       => 'Email must be a valid email address.',
            'email.unique'      => 'Email has already been taken.',
            'password.required' => 'Password is required.',
        ];
    }
}
