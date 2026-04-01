<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !$user->role) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Check if user has any of the allowed roles
        // We assume $roles contains role names
        if (in_array($user->role->role_name, $roles)) {
            return $next($request);
        }

        return response()->json(['message' => 'Forbidden: Insufficient permissions'], 403);
    }
}
