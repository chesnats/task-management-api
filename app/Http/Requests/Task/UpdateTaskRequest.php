<?php

namespace App\Http\Requests\Task;

use App\Http\Requests\Base\ApiRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends ApiRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $taskId = $this->route('task') ? $this->route('task')->id : null;

        return [
            'title'       => ['sometimes','string','max:255', Rule::unique('tasks', 'title')->ignore($taskId),],
            'description' => ['sometimes', 'nullable', 'string'],
            'completed'   => ['sometimes', 'boolean'],
            'user_id'     => ['sometimes', 'exists:users,id'],
        ];
    }

    public function messages()
    {
        return [
            'title.string'       => 'Title must be a string.',
            'title.max'          => 'Title may not be greater than 255 characters.',
            'title.unique'       => 'Title already exists.',
            'description.string' => 'Description must be a string.',
            'completed.boolean'  => 'Completed must be true or false.',
            'user_id.exists'     => 'User not found.',
        ];
    }
}
