<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // GET /api/tasks - with role-based filtering
    public function index(Request $request)
    {
        $authUser = $request->user();
        $query = Task::query();

        if ($authUser->isAdmin()) {
            $query = Task::query();
        } elseif ($authUser->isTeamLeader()) {
            $query = Task::whereIn('user_id', function ($subquery) use ($authUser) {
                $subquery->select('id')
                         ->from('users')
                         ->where('team_id', $authUser->team_id)
                         ->orWhere('id', $authUser->id);
            });
        } else {
            $query = Task::where('user_id', $authUser->id);
        }

        if ($request->query('include') === 'user') {
            $query->with('user');
        }

        return response()->json($query->get(), 200);
    }
    
    // GET /api/tasks/{task}/user
    public function user($id)
    {
        $task = Task::find($id);

        if (!$task) {
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
            'task'    => $task
        ], 201);
    }

    // PATCH /api/tasks/{task} - with role-based authorization
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $authUser = $request->user();
        $taskOwner = $task->user;

        if ($authUser->isAdmin()) {
        } elseif ($authUser->isTeamLeader()) {
            if ($taskOwner->team_id !== $authUser->team_id && $taskOwner->id !== $authUser->id) {
                return response()->json(['message' => 'Unauthorized to update this task'], 403);
            }
        } else {
            if ($taskOwner->id !== $authUser->id) {
                return response()->json(['message' => 'Unauthorized to update this task'], 403);
            }
        }

        $validated = $request->validated();

        $task->update($validated);

        return response()->json($task, 200);
    }

    // DELETE /api/tasks/{task}
    public function destroy($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully.'], 200);
    }
}
