<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Exports\TeamsExport;
use App\Imports\TeamsImport;
use App\Http\Requests\Team\StoreTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use App\Http\Requests\Team\ImportTeamRequest;
use App\Http\Traits\IncludesTrait;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TeamController extends Controller
{
    use IncludesTrait;
    
    // GET /api/teams - with role-based filtering
    public function index(Request $request)
    {
        $user = $request->user();
        
        // 1. Determine base query based on role
        $query = $user->isAdmin() ? Team::query() : Team::where('id', $user->team_id);

        // 2. Allow Admin to see trashed teams if they add ?trashed=true to the URL
        if ($user->isAdmin() && $request->has('trashed')) {
            $query->withTrashed();
        }

        $includes = $this->parseIncludes($request, ['users']);
        
        return response()->json($query->with($includes)->get(), 200);
    }

    // POST /api/teams - Only Admin can create teams
    public function store(StoreTeamRequest $request)
    {

        $validated = $request->validated();

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/teams'), $filename);
            $validated['avatar'] = $filename;
        }

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

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/teams'), $filename);
            $validated['avatar'] = $filename;
        }

        $team->update($validated);

        return response()->json($team, 200);
    }

    // DELETE /api/teams/{team} - Only Admin can delete teams
    public function destroy(Team $team)
    {
        $team->delete();

        return response()->json(['message' => 'Team deleted successfully'], 200);
    }
    public function restore($id)
    {
        $team = Team::onlyTrashed()->findOrFail($id);
        $team->restore();
        return response()->json(['message' => 'Team restored successfully']);
    }

    // GET /api/export/teams
    public function export()
    {
        $fileName = 'teams_export_' . now()->format('Y-m-d_His') . '.xlsx';
        return Excel::download(new TeamsExport, $fileName);
    }

    // POST /api/import/teams
    public function import(ImportTeamRequest $request)
    {
        try {
            Excel::import(new TeamsImport, $request->file('file'));
            
            return response()->json(['message' => 'Teams imported successfully!'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // PATCH /api/teams/{team}/avatar - Update team avatar with role-based authorization
    public function updateAvatar(Request $request, Team $team)
    {
        $authUser = $request->user();

        // Authorization
        if (!$authUser->isAdmin() && $authUser->team_id !== $team->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validation
        $request->validate([
            'avatar' => ['required', 'image', 'max:5120'],
        ]);

        // Delete old avatar if it exists
        if ($team->avatar) {
            $oldPath = public_path('uploads/teams/' . $team->avatar);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        // Process new upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/teams'), $filename);

            $team->update(['avatar' => $filename]);
        }

        return response()->json([
            'message' => 'Team avatar updated successfully',
            'team' => $team
        ], 200);
    }

    // POST /api/teams/{team}/restore - Restore a soft-deleted team
    public function restoreTeam($id, Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized. Only admins can restore teams.'], 403);
        }

        $team = Team::withTrashed()->findOrFail($id);

        $team->restore();

        return response()->json([
            'message' => 'Team restored successfully',
            'team'    => $team
        ], 200);
    }
}
