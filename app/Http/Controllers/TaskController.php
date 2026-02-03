<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // GET /api/tasks?include=user
    public function index(Request $request)
    {
        $query = Task::query();

        if ($request->query('include') === 'user') {
            $query->with('user');
        }

        return response()->json($query->get(), 200);
    }
    
    // GET /api/tasks/{task}/user
    public function user($id)
    {
        $task = Task::find($id);

        if (! $task) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        return response()->json($task->user, 200);
    }

    // POST /api/tasks
    public function store(StoreTaskRequest $request)
    {
        $data = $request->validated();

        $task = Task::create($data);

        return response()->json([
            'message' => 'Task created successfully.',
            'task' => $task
        ], 201);
    }

    // PUT/PATCH /api/tasks/{task}
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $data = $request->validated();

        $task->update($data);

        return response()->json($task, 200);
    }

    // DELETE /api/tasks/{task}
    public function destroy($id)
    {
        $task = Task::find($id);

        if (! $task) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully.'], 200);
    }
}
