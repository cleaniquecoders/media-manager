<?php

namespace CleaniqueCoders\MediaManager\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeMediaManager
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $gate = config('media-manager.authorization.gate');

        // If no gate is configured, allow all authenticated users
        if (is_null($gate)) {
            return $next($request);
        }

        // Check the configured gate
        if (Gate::denies($gate)) {
            abort(403, 'You are not authorized to access the Media Manager.');
        }

        return $next($request);
    }
}
