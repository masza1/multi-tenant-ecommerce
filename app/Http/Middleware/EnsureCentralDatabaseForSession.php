<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnsureCentralDatabaseForSession
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Temporarily switch to central connection for session operations
        // This ensures session table queries use central database
        $originalDefault = DB::getDefaultConnection();
        
        // Set central as default for session operations
        DB::setDefaultConnection('central');
        
        try {
            $response = $next($request);
        } finally {
            // Restore original default connection
            DB::setDefaultConnection($originalDefault);
        }
        
        return $response;
    }
}
