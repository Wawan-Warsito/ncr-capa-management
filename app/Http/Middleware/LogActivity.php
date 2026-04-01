<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ActivityLog;

class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log modification requests (POST, PUT, DELETE, PATCH)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $user = $request->user();
            
            if ($user) {
                $path = $request->path();
                $method = $request->method();
                $ip = $request->ip();
                
                // We can't easily capture the specific entity ID or type here generically 
                // without inspecting the route or response, but we can log the request payload.
                // For detailed business logic logging (like "NCR Created"), 
                // it's better done in the Controller/Service as we did.
                // This middleware can serve as a raw access log.
                
                // For now, we'll skip duplicate logging if the controller already logged it via ActivityLog model.
                // Or we can log "API Request" as a generic activity.
                
                /*
                ActivityLog::create([
                    'log_name' => 'API_Access',
                    'description' => "$method $path",
                    'causer_type' => get_class($user),
                    'causer_id' => $user->id,
                    'properties' => [
                        'ip' => $ip,
                        'input' => $request->except(['password', 'password_confirmation']),
                        'status' => $response->getStatusCode()
                    ]
                ]);
                */
            }
        }

        return $response;
    }
}
