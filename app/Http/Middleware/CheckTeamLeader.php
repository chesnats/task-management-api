<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckTeamLeader
{
    /**
     *
     * Admins can manage any team; team leaders can only manage their own team.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $teamParam
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $teamParam = 'team')
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized - Please login first'], 401);
        }

        if ($user->isAdmin()) {
            return $next($request);
        }

        if ($user->isTeamLeader()) {
            $team = $request->route($teamParam);
            
            if ($team && $team->id !== $user->team_id) {
                return response()->json([
                    'message' => 'Forbidden - You can only manage your own team',
                ], 403);
            }

            return $next($request);
        }

        return response()->json([
            'message' => 'Forbidden - You do not have permission to manage teams',
        ], 403);
    }
}
