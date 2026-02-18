<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Http\Traits\IncludesTrait;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    use IncludesTrait;
    
    // GET /api/teams - with role-based filtering
    public function index(Request $request)
    {
        $authUser = $request->user();
        $includes = $this->parseIncludes($request, ['users']);
        
        if ($authUser->isAdmin()) {
            $query = Team::query();
        } elseif ($authUser->team_id) {
            $query = Team::where('id', $authUser->team_id);
        } else {
            $query = Team::query();
        }
        
        if ($includes) {
            $query->with($includes);
        }

        return response()->json($query->get(), 200);
    }

    // POST /api/teams - Only Admin can create teams
    public function store(StoreTeamRequest $request)
    {
        $validated = $request->validated();

        $team = Team::create($validated);

        return response()->json([
            'message' => 'Team created successfully',
            'team'    => $team
        ], 201);
    }

    // GET /api/teams/{team} - with role-based authorization
    public function show(Team $team, Request $request)
    {
        $authUser = $request->user();
        
        // Allow if: admin, has no team (can browse), or belongs to the team
        if (!$authUser->isAdmin() && $authUser->team_id && $team->id !== $authUser->team_id) {
            return response()->json(['message' => 'Unauthorized to view this team'], 403);
        }
        
        $includes = $this->parseIncludes($request, ['users']);
        
        if ($includes) {
            $team->load($includes);
        }

        return response()->json($team, 200);
    }

    // PATCH /api/teams/{team} - Only Admin can update teams
    public function update(UpdateTeamRequest $request, Team $team)
    {
        $validated = $request->validated();

        $team->update($validated);

        return response()->json($team, 200);
    }

    // DELETE /api/teams/{team} - Only Admin can delete teams
    public function destroy(Team $team)
    {
        $team->delete();

        return response()->json(['message' => 'Team deleted successfully'], 200);
    }
}
