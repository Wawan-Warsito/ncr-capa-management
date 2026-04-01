<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDepartment
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->department_id) {
            return response()->json(['message' => 'Unauthorized: No department assigned'], 401);
        }

        // Logic to restrict access based on department if needed
        // For now, it just ensures the user has a department
        
        return $next($request);
    }
}
