<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && $user->must_change_password) {
            // Admin users shouldn't be forced to change a default password
            if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
                return $next($request);
            }
            // allow access to change password routes and logout
            if ($request->routeIs('password.change') || $request->routeIs('password.change.update') || $request->routeIs('logout')) {
                return $next($request);
            }

            return redirect()->route('password.change');
        }

        return $next($request);
    }
}
