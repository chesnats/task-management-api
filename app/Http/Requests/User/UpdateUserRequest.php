<?php

namespace App\Http\Requests\User;

use App\Http\Requests\Base\ApiRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $userId = $this->route('user') ? $this->route('user')->id : null;

        return [
            'name'     => ['sometimes','string','max:255', Rule::unique('users', 'name')->ignore($userId),],
            'email'    => ['sometimes','email', Rule::unique('users', 'email')->ignore($userId),],
            'password' => ['sometimes','string','min:8'],
            'avatar'   => ['nullable','image','max:5120'],
            'team_id'  => ['sometimes','nullable','exists:teams,id'],
        ];
    }

    public function messages()
    {
        return [
            'name.string'        => 'Name must be a string.',
            'name.max'           => 'Name may not be greater than 255 characters.',
            'name.unique'        => 'Name has already been taken.',
            'email.email'        => 'Please provide a valid email address.',
            'email.unique'       => 'Email has already been taken.',
            'password.string'    => 'Password must be a string.',
            'password.min'       => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Password confirmation does not match.',
            'team_id.exists'     => 'The selected team does not exist.',
        ];
    }
}
