<?php

namespace App\Http\Requests\Team;

use App\Http\Requests\Base\ApiRequest;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $teamId = $this->route('team') ? $this->route('team')->id : null;

        return [
            'name'        => ['sometimes', 'string', 'max:255', Rule::unique('teams', 'name')->ignore($teamId)],
            'description' => ['sometimes', 'nullable', 'string'],
            'avatar'      => ['nullable', 'image', 'max:5120'],
        ];
    }

    public function messages()
    {
        return [
            'name.string'        => 'Team name must be a string.',
            'name.max'           => 'Team name may not be greater than 255 characters.',
            'name.unique'        => 'Team name has already been taken.',
            'description.string' => 'Description must be a string.',
        ];
    }
}
