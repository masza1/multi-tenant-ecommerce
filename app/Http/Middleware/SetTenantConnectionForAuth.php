<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetTenantConnectionForAuth
{
    /**
     * Handle an incoming request.
     * 
     * Set the default database connection to tenant connection
     * if tenancy is initialized. This ensures auth queries use tenant database.
     */
    public function handle(Request $request, Closure $next)
    {
        // If tenancy is initialized, switch default connection to tenant
        if (tenancy()->initialized) {
            DB::setDefaultConnection('tenant');
        }

        return $next($request);
    }
}
