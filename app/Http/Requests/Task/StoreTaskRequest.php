<?php

namespace App\Http\Requests\Task;

use App\Http\Requests\Base\ApiRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title'       => ['required', 'string', 'max:255', 'unique:tasks,title'],
            'description' => ['sometimes', 'nullable', 'string'],
            'completed'   => ['sometimes', 'boolean'],
            'user_id'     => ['required', Rule::exists('users', 'id')->whereNull('deleted_at')],
        ];
    }

    public function messages()
    {
        return [
            'title.required'     => 'Title is required.',
            'title.string'       => 'Title must be a string.',
            'title.max'          => 'Title may not be greater than 255 characters.',
            'title.unique'       => 'Title already exists.',
            'description.string' => 'Description must be a string.',
            'completed.boolean'  => 'Completed must be true or false.',
            'user_id.required'   => 'User ID is required.',
            'user_id.exists'     => 'User not found.',
        ];
    }
}
