<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Checks authentication and role authorization.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Resolve user: prefer the request resolver, otherwise try Sanctum guard
        $user = $request->user() ?? auth('sanctum')->user();

        if ($user) {
            // Ensure the request user resolver returns the resolved user
            $request->setUserResolver(fn () => $user);
        } else {
            return response()->json([
                'message' => 'Unauthorized - Please login first',
            ], 401);
        }

        // If no roles specified, just check authentication
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user has one of the required roles (case-insensitive)
        $normalizedRoles = array_map(fn($r) => strtolower((string) $r), $roles);
        $userRole = strtolower((string) $request->user()->role);

        if (!in_array($userRole, $normalizedRoles, true)) {
            return response()->json([
                'message' => 'Forbidden - You do not have permission to access this resource',
                'required_role' => $roles,
                'your_role' => $request->user()->role,
            ], 403);
        }

        return $next($request);
    }
}
