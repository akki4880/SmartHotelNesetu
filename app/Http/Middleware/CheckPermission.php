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
     * @param  Closure(Request): (Response)  $next
     */
    public function handle($request, Closure $next, $module, $action)
    {
        $user = auth()->user();
        if (!$user || !$user->hasPermission($module, $action)) {
            return response()->view('pages.errors.index', [
                'code' => 403,
                'title' => 'Access Denied',
                'message' => 'You do not have permission to access this page.',
                'description' => 'Please contact your administrator if you believe this is an error.',
                'breadcrumb' => '403 Error',
            ], 403);
        }

        return $next($request);
    }
}
