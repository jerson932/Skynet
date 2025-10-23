<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceHttps
{
    public function handle(Request $request, Closure $next)
    {
        // No forzar HTTPS en health checks
        if ($request->is('health', 'status', 'up')) {
            return $next($request);
        }

        if (!$request->isSecure() && app()->environment('production')) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}