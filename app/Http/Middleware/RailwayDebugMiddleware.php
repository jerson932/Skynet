<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RailwayDebugMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Log para debugging en Railway
        if (app()->environment('production')) {
            Log::info('Railway Request Debug', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
                'headers' => $request->headers->all(),
            ]);
        }

        return $next($request);
    }
}