<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;

class TeamController extends Controller
{
    // GET /api/teams - Anyone can view teams
    public function index()
    {
        return response()->json(Team::all(), 200);
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

    // GET /api/teams/{team} - Anyone can view specific team
    public function show(Team $team)
    {
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
