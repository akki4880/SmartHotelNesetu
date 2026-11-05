<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, ...$roles)
    {
        // User not logged in
        if (!auth()->check()) {
            return response()->view('pages.errors.index', [
                'code' => 403,
                'title' => 'Unauthorized Access',
                'message' => 'You must be logged in to access this page.',
                'description' => 'Please login and try again.',
                'breadcrumb' => '403 Error',
            ], 403);
        }

        // User does not have the required role
        if (!in_array(auth()->user()->user_role_id, $roles)) {
            return response()->view('pages.errors.index', [
                'code' => 403,
                'title' => 'Access Denied',
                'message' => 'You do not have permission to access this page.',
                'description' => 'Please contact your administrator if you believe this is an error.',
                'breadcrumb' => '403 Error',
            ], 403);
        }

        // User has permission, continue request
        return $next($request);
    }
}
