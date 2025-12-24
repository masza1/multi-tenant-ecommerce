<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Contracts\TenantWithDatabase;

class MigrateTenantDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenant;

    public function __construct(TenantWithDatabase $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle()
    {
        try {
            \Log::info('Starting migrations for tenant', ['tenant_id' => $this->tenant->getTenantKey()]);

            // Get tenant database config
            $dbName = config('tenancy.database.prefix') . $this->tenant->getTenantKey();
            
            // Create a temporary connection to the tenant database
            $tenantConfig = config('database.connections.central');
            $tenantConfig['database'] = $dbName;
            
            DB::purge('temp_migrate');
            config(['database.connections.temp_migrate' => $tenantConfig]);
            
            // Run migrations directly on the tenant database
            $exitCode = Artisan::call('migrate', [
                '--database' => 'temp_migrate',
                '--path' => database_path('migrations/tenant'),
                '--realpath' => true,
                '--force' => true,
            ]);

            $output = Artisan::output();

            \Log::info('Migrations completed', [
                'tenant_id' => $this->tenant->getTenantKey(),
                'database' => $dbName,
                'exit_code' => $exitCode,
                'output' => $output,
            ]);
            
            // Clean up the temporary connection
            DB::purge('temp_migrate');
            
        } catch (\Exception $e) {
            \Log::error('Migration failed for tenant', [
                'tenant_id' => $this->tenant->getTenantKey(),
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            throw $e;
        }
    }
}
