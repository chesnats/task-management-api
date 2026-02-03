<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\UpdateUserRequest;
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
    
    // GET /api/users/{user}/tasks
    public function tasks($id)
    {
        $user = User::find($id);
        
        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
            }
            
            return response()->json($user->tasks, 200);
    }

    // PUT/PATCH /api/users/{user}
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        $user->update($data);

        return response()->json($user, 200);
    }
    
    // DELETE /api/users/{user}
    public function destroy($id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted successfully.'], 200);
    }
}
