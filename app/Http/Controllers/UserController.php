<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\User\UpdateUserRequest;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // GET /api/users - with role-based filtering
    public function index(Request $request)
    {
        $authUser = $request->user();
        $query = User::query();

        if ($authUser->isAdmin()) {
            $query = User::query();
        } elseif ($authUser->isTeamLeader()) {
            $query = User::where('team_id', $authUser->team_id)
                        ->orWhere('id', $authUser->id);
        } else {
            $query = User::where('id', $authUser->id);
        }

        if ($request->query('include') === 'tasks') {
            $query->with('tasks');
        }

        return response()->json($query->get(), 200);
    }

    // POST /api/users - with role-based authorization
    public function store(Request $request)
    {
        $authUser = $request->user();

        if (!$authUser->isAdmin() && !$authUser->isTeamLeader()) {
            return response()->json(['message' => 'Unauthorized to create users'], 403);
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'role'     => 'sometimes|in:user,team_leader',
            'team_id'  => 'sometimes|required_if:role,team_leader|nullable|exists:teams,id',
        ]);

        if ($authUser->isTeamLeader()) {
            $validated['team_id'] = $authUser->team_id;
            $validated['role'] = 'user';
        } else {
            if (!isset($validated['team_id']) || $validated['team_id'] === null) {
                return response()->json(['message' => 'team_id is required when admin creates a user'], 400);
            }
        }

        $user = User::create($validated);

        return response()->json([
            'message' => 'User created successfully',
            'user'    => $user
        ], 201);
    }
    
    // GET /api/users/{user}/tasks
    public function tasks(Request $request, $id)
    {
        $authUser = $request->user();
        $user = User::find($id);
        
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($authUser->isAdmin()) {
        } elseif ($authUser->isTeamLeader()) {
            if ($user->team_id !== $authUser->team_id && $user->id !== $authUser->id) {
                return response()->json(['message' => 'Unauthorized to view these tasks'], 403);
            }
        } else {
            if ($user->id !== $authUser->id) {
                return response()->json(['message' => 'Unauthorized to view these tasks'], 403);
            }
        }

        return response()->json($user->tasks, 200);
    }

    // PATCH /api/users/{user} - with role-based authorization
    public function update(UpdateUserRequest $request, User $user)
    {
        $authUser = $request->user();

        if ($authUser->isAdmin()) {
        } elseif ($authUser->isTeamLeader()) {
            if ($user->team_id !== $authUser->team_id && $user->id !== $authUser->id) {
                return response()->json(['message' => 'Unauthorized to update this user'], 403);
            }
        } else {
            if ($user->id !== $authUser->id) {
                return response()->json(['message' => 'Unauthorized to update this user'], 403);
            }
        }

        $validated = $request->validated();

        $user->update($validated);

        return response()->json($user, 200);
    }
    
    // DELETE /api/users/{user}
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.'], 200);
    }
}
