<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // GET /tasks
    public function index()
    {
        return response()->json(Task::all());
    }

    // GET /tasks/{id}
    public function show($id)
    {
        $task = Task::find($id);
        return response()->json($task);
    }

    // POST /tasks
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'completed' => 'nullable|boolean',
            'user_id' => 'nullable|integer|exists:users,id',
        ]);

        $task = Task::create($data);

        return response()->json($task, 201);
    }

    // PATCH /tasks/{id}
    public function update(Request $request, $id)
    {
        $task = Task::find($id);
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'completed' => 'sometimes|boolean',
            'user_id' => 'sometimes|nullable|integer|exists:users,id',
        ]);

        $task->update($data);

        return response()->json($task);
    }

    // DELETE /tasks/{id}
    public function destroy($id)
    {
        $task = Task::find($id);

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully'], 200);
    }
}
