<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // GET /api/users?include=tasks
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->query('include') === 'tasks') {
            $query->with('tasks');
        }

        return response()->json($query->get(), 200);
    }

    // GET /api/users/{user}?include=tasks
    public function show(Request $request, User $user)
    {
        if ($request->query('include') === 'tasks') {
            $user->load('tasks');
        }

        return response()->json($user, 200);
    }

    // POST /api/users
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);
        
        if (User::where('name', $data['name'])->exists()) {
            return response()->json(['message' => 'Name already exists'], 400);
        }

        if (User::where('email', $data['email'])->exists()) {
            return response()->json(['message' => 'Email already exists'], 400);
        }

        $user = User::create($data);

        return response()->json($user, 201);
    }
    
    // PUT /api/users/{user}
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'sometimes|string|max:255',
            'email'    => 'sometimes|email',
            'password' => 'sometimes|min:6|confirmed',
        ]);

        if (isset($data['name']) &&
            User::where('name', $data['name'])->where('id', '!=', $user->id)->exists()) {
            return response()->json(['message' => 'Name already exists'], 400);
        }

        if (isset($data['email']) &&
            User::where('email', $data['email'])->where('id', '!=', $user->id)->exists()) {
            return response()->json(['message' => 'Email already exists'], 400);
        }

        $user->update($data);

        return response()->json($user, 200);
    }

    // GET /api/users/{user}/tasks
    public function tasks(User $user)
    {
        return response()->json($user->tasks()->get(), 200);
    }

    // DELETE /api/users/{user}
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'User deleted successfully.'], 204);
    }
}
