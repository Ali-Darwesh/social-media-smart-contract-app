<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckScope
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$scopes)
    {
        if (!$request->user() || !$request->user()->token()) {
            abort(403, 'Unauthorized - No token');
        }

        foreach ($scopes as $scope) {
            if (!$request->user()->tokenCan($scope)) {
                abort(403, 'Unauthorized - Missing scope: '.$scope);
            }
        }

        return $next($request);
    }
}
