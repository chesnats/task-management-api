<?php

namespace App\Http\Controllers;

use App\Models\Task;
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

    // GET /api/tasks/{task}?include=user
    public function show(Request $request, Task $task)
    {
        if (! $task) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        if ($request->query('include') === 'user') {
            $task->load('user');
        }

        return response()->json($task, 200);
    }

    // POST /api/tasks
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'completed'   => 'sometimes|boolean',
            'user_id'     => 'required|exists:users,id',
        ]);

        if (isset($data['title']) && Task::where('title', $data['title'])->exists()) {
            return response()->json(['message' => 'Title already exist'], 400);
        }

        $task = Task::create($data);

        return response()->json($task, 201);
    }

    // PUT/PATCH /api/tasks/{task}
    public function update(Request $request, Task $task)
    {
        if (! $task) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $data = $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'completed'   => 'sometimes|boolean',
            'user_id'     => 'sometimes|exists:users,id',
        ]);

        if (isset($data['title']) && Task::where('title', $data['title'])->where('id', '!=', $task->id)->exists()) {
            return response()->json(['message' => 'Title already exist'], 400);
        }

        $task->update($data);

        return response()->json($task, 200);
    }

    // DELETE /api/tasks/{task}
    public function destroy(Task $task)
    {
        if (! $task) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully.'], 204);
    }

    // GET /api/tasks/{task}/user
    public function user(Task $task)
    {
        $user = $task->user;

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user, 200);
    }
}
