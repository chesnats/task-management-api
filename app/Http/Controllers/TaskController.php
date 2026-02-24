<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    // GET /api/tasks - with role-based filtering
    public function index(Request $request)
    {
        $auth = $request->user();
        $query = Task::query();

        // Role-based filtering
        if ($auth->isTeamLeader()) {
            $query->whereHas('user', fn($q) => $q->where('team_id', $auth->team_id));
        } elseif (!$auth->isAdmin()) {
            $query->where('user_id', $auth->id);
        }

        // Include trashed if Admin and requested
        if ($auth->isAdmin() && $request->has('trashed')) {
            $query->withTrashed();
        }

        return response()->json($query->with($request->include === 'user' ? ['user'] : [])->get());
    }

    // GET /api/tasks/{task}/user
    public function user($id, Request $request)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        if (!$this->canManageTask($request->user(), $task)) {
            return response()->json(['message' => 'Unauthorized to view this task user'], 403);
        }

        return response()->json($task->user, 200);
    }

    // POST /api/tasks
    public function store(StoreTaskRequest $request)
    {
        $authUser = $request->user();
        $data = $request->validated();
        $assignee = User::findOrFail($data['user_id']);

        if (!$this->canAssignTaskTo($authUser, $assignee)) {
            return response()->json(['message' => 'Unauthorized to assign task to this user'], 403);
        }

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

        if (!$this->canManageTask($authUser, $task)) {
            return response()->json(['message' => 'Unauthorized to update this task'], 403);
        }

        $validated = $request->validated();

        if (array_key_exists('user_id', $validated)) {
            $assignee = User::findOrFail($validated['user_id']);

            if (!$this->canAssignTaskTo($authUser, $assignee)) {
                return response()->json(['message' => 'Unauthorized to reassign task to this user'], 403);
            }
        }

        $task->update($validated);

        return response()->json($task, 200);
    }

    // DELETE /api/tasks/{task}
    public function destroy($id, Request $request)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found.'], 404);
        }

        if (!$this->canManageTask($request->user(), $task)) {
            return response()->json(['message' => 'Unauthorized to delete this task'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully.'], 200);
    }

    // POST /api/tasks/restore/{id} - Restore a soft-deleted task
    public function restore($id, Request $request)
    {
        $task = Task::withTrashed()->findOrFail($id);
        $auth = $request->user();

        // Authorization: Only Admin or the Team Leader of the task owner can restore
        if (!$auth->isAdmin()) {
            $taskOwner = $task->user;
            if (!$taskOwner || !$auth->isTeamLeader() || $taskOwner->team_id !== $auth->team_id) {
                return response()->json(['message' => 'Unauthorized to restore this task'], 403);
            }
        }

        $task->restore();

        return response()->json([
            'message' => 'Task restored successfully',
            'task' => $task
        ], 200);
    }

    private function canManageTask(User $authUser, Task $task): bool
    {
        if ($authUser->isAdmin()) {
            return true;
        }

        $taskOwner = User::withTrashed()->find($task->user_id);

        if (!$taskOwner) {
            return false;
        }

        if ($authUser->isTeamLeader()) {
            return $taskOwner->team_id === $authUser->team_id || $taskOwner->id === $authUser->id;
        }

        return $taskOwner->id === $authUser->id;
    }

    private function canAssignTaskTo(User $authUser, User $assignee): bool
    {
        if ($authUser->isAdmin()) {
            return true;
        }

        if ($authUser->isTeamLeader()) {
            return $assignee->team_id === $authUser->team_id || $assignee->id === $authUser->id;
        }

        return $assignee->id === $authUser->id;
    }
}
