<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // GET /api/users
    public function index()
    {
        return User::all();
    }

    // GET /api/users/{user}
    public function show(User $user)
    {
        return $user;
    }

    // POST /api/users
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);
        
        if (User::where('name', $data['name'])->exists()) {
            return response()->json(['message' => 'Name already exists'], 400);
        }

        if (User::where('email', $data['email'])->exists()) {
            return response()->json(['message' => 'Email already exists'], 400);
        }

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json($user, 201);
    }
    
    // PUT /api/users/{user}
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
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

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json($user, 200);
    }

    // DELETE /api/users/{user}
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'User deleted successfully'], 204);
    }
}
