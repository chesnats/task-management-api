<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Traits\IncludesTrait;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use IncludesTrait;
    
    // GET /api/users - with role-based filtering
    public function index(Request $request)
    {
        $authUser = $request->user();
        $query = User::query();

        if ($authUser->isAdmin()) {
            $query = User::query();
            // ONLY Admin can toggle trashed records
            if ($request->has('trashed') && $request->query('trashed') === 'true') {
                $query->withTrashed();
            }
        } elseif ($authUser->isTeamLeader()) {
            $query = User::where('team_id', $authUser->team_id)
                        ->orWhere('id', $authUser->id);
        } else {
            $query = User::where('id', $authUser->id);
        }

        $includes = $this->parseIncludes($request, ['team', 'tasks']);
        if ($includes) {
            $query->with($includes);
        }

        return response()->json($query->get(), 200);
    }

    // POST /api/users - with role-based authorization
    public function store(StoreUserRequest $request)
    {
        $authUser = $request->user();

        if (!$authUser->isAdmin() && !$authUser->isTeamLeader()) {
            return response()->json(['message' => 'Unauthorized to create users'], 403);
        }

        $validated = $request->validated();

        if ($authUser->isTeamLeader()) {
            $validated['team_id'] = $authUser->team_id;
            $validated['role'] = 'user';
        } else {
            if (!isset($validated['team_id']) || $validated['team_id'] === null) {
                return response()->json(['message' => 'team_id is required when admin creates a user'], 400);
            }
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/users'), $filename);
            $validated['avatar'] = $filename;
        }

        $user = User::create($validated);

        return response()->json([
            'message' => 'User created successfully',
            'user'    => $user
        ], 201);
    }

    // GET /api/users/{user} - Show single user with optional includes
    public function show(User $user, Request $request)
    {
        $authUser = $request->user();

        if ($authUser->isAdmin()) {
        } elseif ($authUser->isTeamLeader()) {
            if ($user->team_id !== $authUser->team_id && $user->id !== $authUser->id) {
                return response()->json(['message' => 'Unauthorized to view this user'], 403);
            }
        } else {
            if ($user->id !== $authUser->id) {
                return response()->json(['message' => 'Unauthorized to view this user'], 403);
            }
        }

        $includes = $this->parseIncludes($request, ['team', 'tasks']);
        if ($includes) {
            $user->load($includes);
        }

        return response()->json($user, 200);
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

        // Only admins can update team_id
        if (isset($request->validated()['team_id']) && !$authUser->isAdmin()) {
            return response()->json(['message' => 'Only admins can update user team_id'], 403);
        }

        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/users'), $filename);
            $validated['avatar'] = $filename;
        }

        $user->update($validated);

        return response()->json($user, 200);
    }

    // PATCH /api/users/{user}/avatar - update avatar (admin, team leader for own team, or user themself)
    public function updateAvatar(Request $request, User $user)
    {
        $authUser = $request->user();

        if ($authUser->isAdmin()) {
        } elseif ($authUser->isTeamLeader()) {
            if ($user->team_id !== $authUser->team_id && $user->id !== $authUser->id) {
                return response()->json(['message' => 'Unauthorized to update this user avatar'], 403);
            }
        } else {
            if ($user->id !== $authUser->id) {
                return response()->json(['message' => 'Unauthorized to update this user avatar'], 403);
            }
        }

        $validated = $request->validate([
            'avatar' => ['required', 'image', 'max:5120'],
        ]);

        // remove old avatar file if exists
        if ($user->avatar) {
            $oldPath = public_path('uploads/users/' . $user->avatar);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $file = $request->file('avatar');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('uploads/users'), $filename);

        $user->update(['avatar' => $filename]);

        return response()->json(['message' => 'Avatar updated', 'user' => $user], 200);
    }
    
    // DELETE /api/users/{user}
    public function destroy($id, Request $request)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $authUser = $request->user();

        if ($authUser->isAdmin()) {
        } elseif ($authUser->isTeamLeader()) {
            if ($user->team_id !== $authUser->team_id) {
                return response()->json(['message' => 'Unauthorized to delete this user'], 403);
            }
        } else {
            return response()->json(['message' => 'Unauthorized to delete users'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.'], 200);
    }

    // POST /api/users/{id}/restore - Only Admin can restore users
    public function restore($id, Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user = User::withTrashed()->findOrFail($id);
        $user->restore();

        return response()->json([
            'message' => 'User restored successfully',
            'user' => $user
        ], 200);
    }
}
