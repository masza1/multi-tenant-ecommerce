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
        // Only check if tenancy is active (tenant has been identified)
        // tenancy()->initialized is a public property, not a method
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
        }

        return $next($request);
    }
}
