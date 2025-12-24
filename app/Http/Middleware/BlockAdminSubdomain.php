<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockAdminSubdomain
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        // Block admin.localhost and admin.localhost:8000
        if (strpos($host, 'admin.localhost') === 0) {
            abort(404, 'Not Found');
        }

        return $next($request);
    }
}
