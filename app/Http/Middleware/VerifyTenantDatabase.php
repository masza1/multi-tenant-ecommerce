<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class VerifyTenantDatabase
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if we're on a tenant domain
        $host = $request->getHost();
        $centralDomains = config('tenancy.central_domains', [
            '127.0.0.1',
            'localhost',
            'localhost:8000',
            '127.0.0.1:8000',
        ]);

        // If it's a central domain, skip verification
        if (in_array($host, $centralDomains, true)) {
            return $next($request);
        }

        // For tenant domains, verify if tenant is initialized and has a database
        if (tenancy()->initialized && tenancy()->tenant) {
            $tenant = tenancy()->tenant;

            try {
                // Use the 'tenant' connection since tenancy is already initialized
                $connection = DB::connection('tenant');

                // Try to query information_schema to check if tenant database has tables
                $result = $connection->select("
                    SELECT 1 FROM information_schema.tables
                    WHERE table_schema = 'public'
                    AND table_name = 'users'
                    LIMIT 1
                ");

                // If no tables found (database likely doesn't exist or is empty), return 404
                if (empty($result)) {
                    \Log::warning('VerifyTenantDatabase: users table not found', [
                        'tenant_id' => $tenant->id,
                    ]);
                    abort(404, __('messages.tenant_not_found_message'));
                }

                \Log::debug('VerifyTenantDatabase: tenant database verified', [
                    'tenant_id' => $tenant->id,
                ]);
            } catch (\Stancl\Tenancy\Exceptions\DatabaseManagerNotRegisteredException $e) {
                // Database manager not registered - likely tenant database not created yet
                \Log::warning('VerifyTenantDatabase: database manager not registered', [
                    'tenant_id' => $tenant->id,
                    'error' => $e->getMessage(),
                ]);
                abort(503, __('messages.tenant_not_ready_message', ['Default' => 'Tenant database is not ready yet. Please try again in a moment.']));
            } catch (\PDOException $e) {
                // If connection fails (database doesn't exist), return 404
                if (strpos($e->getMessage(), 'does not exist') !== false ||
                    strpos($e->getMessage(), 'FATAL') !== false ||
                    strpos($e->getMessage(), 'SQLSTATE') !== false) {
                    \Log::warning('VerifyTenantDatabase: database not found', [
                        'tenant_id' => $tenant->id,
                        'error' => $e->getMessage(),
                    ]);
                    abort(404, __('messages.tenant_not_found_message'));
                }
                // Re-throw other database errors
                throw $e;
            } catch (\Exception $e) {
                \Log::error('VerifyTenantDatabase: unexpected error', [
                    'tenant_id' => $tenant->id ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
                // For other exceptions, re-throw
                throw $e;
            }
        } elseif (!tenancy()->initialized || !tenancy()->tenant) {
            // If tenancy is not initialized, it means tenant domain doesn't exist
            // This is a 404 - store not found
            \Log::debug('VerifyTenantDatabase: tenant not found', [
                'host' => $request->getHost(),
            ]);
            
            abort(404, __('messages.tenant_not_found_message'));
        }

        return $next($request);
    }
}
