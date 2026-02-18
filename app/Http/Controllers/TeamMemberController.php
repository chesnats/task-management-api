<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{

    // POST /teams/{team}/members
    public function store(Request $request, Team $team)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ($user->team_id !== null) {
            return response()->json([
                'message' => 'User is already part of another team',
            ], 409);
        }

        $user->update(['team_id' => $team->id]);

        return response()->json([
            'message' => 'Member added to team successfully',
            'user' => $user,
        ], 201);
    }
    
     // DELETE /teams/{team}/members/{userId}
    public function destroy(Request $request, Team $team, $userId)
    {
        $user = User::findOrFail($userId);

        if ($user->team_id !== $team->id) {
            return response()->json([
                'message' => 'User is not a member of this team',
            ], 404);
        }

        $user->update(['team_id' => null]);

        return response()->json([
            'message' => 'Member removed from team successfully',
        ], 200);
    }
}
