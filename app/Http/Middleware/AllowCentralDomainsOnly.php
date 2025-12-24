<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AllowCentralDomainsOnly
{
    /**
     * Handle an incoming request.
     * 
     * This middleware ONLY allows access from central domains.
     * Blocks all tenant subdomain accesses.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $centralDomains = config('tenancy.central_domains', [
            '127.0.0.1',
            'localhost',
            'localhost:8000',
            '127.0.0.1:8000',
        ]);

        // If NOT in central domains, block with 404
        if (!in_array($host, $centralDomains, true)) {
            abort(404, 'Not Found');
        }

        return $next($request);
    }
}
