<?php

namespace App\Http\Requests\Team;

use App\Http\Requests\Base\ApiRequest;

class StoreTeamRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'        => ['required', 'string', 'max:255', 'unique:teams'],
            'description' => ['sometimes', 'nullable', 'string'],
            'avatar'      => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function messages()
    {
        return [
            'name.required'      => 'Team name is required.',
            'name.string'        => 'Team name must be a string.',
            'name.max'           => 'Team name may not be greater than 255 characters.',
            'name.unique'        => 'Team name has already been taken.',
            'description.string' => 'Description must be a string.',
        ];
    }
}
