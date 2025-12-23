<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Facades\Tenancy;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyByDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the current hostname
        $host = $request->getHost();

        // Skip tenancy initialization for localhost (central admin)
        if ($host === 'localhost' || $host === 'admin.localhost' || $host === 'www.localhost') {
            return $next($request);
        }

        // Extract subdomain from the hostname
        $parts = explode('.', $host);

        if (count($parts) >= 2) {
            $subdomain = $parts[0];

            // Try to find the tenant by domain
            try {
                $domain = \App\Models\Domain::where('domain', $host)->first();

                if ($domain && $domain->tenant_id) {
                    // Initialize tenancy for this tenant
                    tenancy()->initialize(
                        $domain->tenant
                    );

                    return $next($request);
                }
            } catch (\Exception $e) {
                // If tenant initialization fails, continue without tenancy
                // This allows the app to handle 404s gracefully
            }
        }

        return $next($request);
    }
}
