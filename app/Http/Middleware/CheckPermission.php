<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            abort(403, 'You must be logged in to access this resource.');
        }

        $user = auth()->user();

        // Check if user has the required permission
        if (!$user->hasPermission($permission)) {
            abort(403, 'You don\'t have permission to perform this action.');
        }

        return $next($request);
    }
}
