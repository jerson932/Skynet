<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TrustRailwayProxy
{
    public function handle(Request $request, Closure $next)
    {
        // Railway usa X-Forwarded-Proto para indicar HTTPS
        if ($request->header('X-Forwarded-Proto') === 'https') {
            $request->server->set('HTTPS', 'on');
            $request->server->set('SERVER_PORT', 443);
        }

        return $next($request);
    }
}