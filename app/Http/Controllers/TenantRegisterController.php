<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Database\Models\Domain;

class TenantRegisterController extends Controller
{
    private const MAX_DATABASE_WAIT_ATTEMPTS = 30;  // Increased for async job execution
    private const DATABASE_WAIT_DELAY_MS = 1000;   // 1 second delay

    public function store(Request $request)
    {
        \Log::debug('TenantRegisterController::store() called', [
            'expectsJson' => $request->expectsJson(),
            'X-Inertia' => $request->header('X-Inertia'),
            'X-Requested-With' => $request->header('X-Requested-With'),
            'Content-Type' => $request->header('Content-Type'),
            'method' => $request->method(),
        ]);

        \Log::debug('Starting form validation');
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required',
                'string',
                'lowercase',
                'regex:/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/',
                'max:50',
                function ($attribute, $value, $fail) {
                    // Check if domain already exists
                    $domain = $value . '.localhost';
                    if (Domain::where('domain', $domain)->exists()) {
                        \Log::debug('Validation failure: subdomain already taken', ['subdomain' => $value]);
                        $fail(__('messages.subdomain_already_taken'));
                    }
                },
            ],
            'admin_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        \Log::debug('Form validation passed');

        $tenant = null;

        try {
            \Log::info('Tenant registration started', [
                'store_name' => $validated['store_name'],
                'subdomain' => $validated['subdomain'],
                'email' => $validated['email'],
            ]);

            // Step 1: Create tenant and domain in central database
            // We use a closure to defer event processing until after transaction commits
            $tenant = null;
            DB::connection('central')->transaction(function () use ($validated, &$tenant) {
                $tenantId = $this->generateUniqueTenantId($validated['store_name']);

                \Log::info('Creating tenant record', [
                    'tenant_id' => $tenantId,
                    'store_name' => $validated['store_name'],
                ]);

                $tenant = Tenant::create([
                    'id' => $tenantId,
                    'name' => $validated['store_name'],
                    'owner_email' => $validated['email'],
                ]);

                \Log::info('Tenant created successfully', ['tenant_id' => $tenant->id]);

                // Create domain mapping
                $domain = $tenant->domains()->create([
                    'domain' => $validated['subdomain'] . '.localhost',
                ]);

                \Log::info('Domain created successfully', [
                    'tenant_id' => $tenant->id,
                    'domain' => $domain->domain,
                ]);
            });

            // Step 2: Wait for database creation and migrations
            // The TenantCreated event should have fired and CreateDatabase/MigrateDatabase jobs should be running
            // We wait for the database to be ready
            \Log::info('Waiting for database creation', ['tenant_id' => $tenant->id]);
            $this->waitForDatabaseReady($tenant);
            \Log::info('Database ready', ['tenant_id' => $tenant->id]);

            // Step 3: Create admin user in tenant database
            \Log::info('Creating admin user in tenant database', ['tenant_id' => $tenant->id]);

            // Create a temporary connection to the tenant database
            $tenantDbConfig = config('database.connections.central');
            $tenantDbConfig['database'] = $tenant->tenancy_db_name ?? (config('tenancy.database.prefix') . $tenant->id);

            DB::purge('tenant_temp');
            config(['database.connections.tenant_temp' => $tenantDbConfig]);

            // Create user with explicit tenant connection
            $newUser = new User([
                'name' => $validated['admin_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'admin',
            ]);

            // Explicitly set the connection on the model instance
            $newUser->setConnection('tenant_temp');
            $newUser->save();

            \Log::info('Admin user created successfully', [
                'tenant_id' => $tenant->id,
                'user_id' => $newUser->id,
                'email' => $newUser->email,
            ]);

            // Step 3: Auto-login the newly created admin user
            \Log::info('Auto-authenticating admin user', [
                'tenant_id' => $tenant->id,
                'user_id' => $newUser->id,
            ]);

            // Refresh tenant from database to ensure internal db keys are loaded
            // The CreateDatabase job has set tenancy_db_connection by now
            $tenant->refresh();

            \Log::debug('Tenant refreshed with internal keys', [
                'tenant_id' => $tenant->id,
                'db_connection' => $tenant->getInternal('db_connection'),
                'db_name' => $tenant->getInternal('db_name'),
            ]);

            // Initialize tenancy for the new tenant
            tenancy()->initialize($tenant);

            // Log in the user
            Auth::guard('web')->login($newUser);

            // End tenancy to return to central context
            tenancy()->end();

            \Log::info('Admin user authenticated successfully', [
                'tenant_id' => $tenant->id,
                'user_id' => $newUser->id,
            ]);

            // Clean up the temporary connection
            DB::purge('tenant_temp');

            // Step 4: Generate redirect URL and return success
            $port = $request->getPort();
            $portStr = in_array($port, [80, 443]) ? '' : ':' . $port;
            $redirectUrl = 'http://' . $validated['subdomain'] . '.localhost' . $portStr . '/admin';

            \Log::info('Tenant registration completed successfully', [
                'tenant_id' => $tenant->id,
                'redirect_url' => $redirectUrl,
            ]);

            return back()
                ->with('success', __('messages.registration_complete'))
                ->with('tenant_redirect_url', $redirectUrl);

        } catch (\Illuminate\Database\QueryException $e) {
            $this->handleRegistrationError($tenant, 'Database query error: ' . $e->getMessage(), $e);
            return back()
                ->with('error', __('messages.store_database_setup_failed'))
                ->withInput();

        } catch (\Exception $e) {
            $this->handleRegistrationError($tenant, 'Unexpected error: ' . $e->getMessage(), $e);

            // Determine the best error message based on the exception
            $errorMessage = __('messages.store_registration_error');

            if (strpos($e->getMessage(), 'timeout') !== false || strpos($e->getMessage(), 'Timeout') !== false) {
                $errorMessage = __('messages.store_initialization_timeout');
            } elseif (strpos($e->getMessage(), 'database') !== false || strpos($e->getMessage(), 'Database') !== false) {
                $errorMessage = __('messages.store_database_setup_failed');
            }

            return back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }

    /**
     * Generate a unique tenant ID from store name
     */
    private function generateUniqueTenantId(string $storeName): string
    {
        $tenantId = Str::slug($storeName);
        $originalId = $tenantId;
        $counter = 1;

        while (Tenant::where('id', $tenantId)->exists()) {
            $tenantId = $originalId . '-' . $counter;
            $counter++;
        }

        return $tenantId;
    }

    /**
     * Wait for database to be created and migrations to run
     *
     * @throws \Exception if database is not ready within timeout
     */
    private function waitForDatabaseReady(Tenant $tenant): void
    {
        $delayMs = self::DATABASE_WAIT_DELAY_MS;
        $tenantDbName = config('tenancy.database.prefix') . $tenant->id . config('tenancy.database.suffix', '');

        for ($attempt = 1; $attempt <= self::MAX_DATABASE_WAIT_ATTEMPTS; $attempt++) {
            try {
                \Log::debug('Checking if tenant database exists', [
                    'tenant_id' => $tenant->id,
                    'expected_db_name' => $tenantDbName,
                    'attempt' => $attempt,
                ]);

                // Check if the database exists in PostgreSQL
                $dbExists = DB::connection('central')->selectOne("
                    SELECT 1 FROM pg_database WHERE datname = ?
                ", [$tenantDbName]);

                if (!$dbExists) {
                    \Log::debug('Tenant database does not exist yet, retrying', [
                        'tenant_id' => $tenant->id,
                        'expected_db_name' => $tenantDbName,
                        'attempt' => $attempt,
                    ]);

                    if ($attempt === self::MAX_DATABASE_WAIT_ATTEMPTS) {
                        throw new \Exception(
                            'Database ' . $tenantDbName . ' was not created within timeout. ' .
                            'Please check if the CreateDatabase job ran successfully.'
                        );
                    }

                    usleep($delayMs * 1000);
                    $delayMs = min($delayMs * 2, 3000);
                    continue;
                }

                \Log::info('Tenant database created successfully', [
                    'tenant_id' => $tenant->id,
                    'database_name' => $tenantDbName,
                    'attempt' => $attempt,
                ]);

                // Database exists, now check if migrations ran
                // Create a temporary connection to the tenant database
                $tenantConfig = config('database.connections.central');
                $tenantConfig['database'] = $tenantDbName;

                DB::purge('temp_tenant');
                config(['database.connections.temp_tenant' => $tenantConfig]);

                $usersTableExists = DB::connection('temp_tenant')->selectOne("
                    SELECT 1 FROM information_schema.tables
                    WHERE table_schema = 'public'
                    AND table_name = 'users'
                ");

                if ($usersTableExists) {
                    \Log::info('Tenant database is fully migrated', [
                        'tenant_id' => $tenant->id,
                        'database_name' => $tenantDbName,
                    ]);
                    return; // Success!
                }

                \Log::debug('Tenant database exists but migrations not complete, retrying', [
                    'tenant_id' => $tenant->id,
                    'attempt' => $attempt,
                ]);

            } catch (\Exception $e) {
                \Log::debug('Error checking database, retrying', [
                    'tenant_id' => $tenant->id,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);
            }

            // Last attempt failed
            if ($attempt === self::MAX_DATABASE_WAIT_ATTEMPTS) {
                throw new \Exception(
                    'Database was not ready within ' . (self::MAX_DATABASE_WAIT_ATTEMPTS * $delayMs) . 'ms. ' .
                    'Please check the logs for more details.'
                );
            }

            // Wait before retrying (exponential backoff)
            usleep($delayMs * 1000);
            $delayMs = min($delayMs * 2, 3000); // Cap at 3 seconds per retry
        }
    }

    /**
     * Handle registration errors
     */
    private function handleRegistrationError(?Tenant $tenant, string $message, \Throwable $exception): void
    {
        if ($tenant) {
            \Log::error('Tenant registration failed after tenant creation', [
                'tenant_id' => $tenant->id,
                'message' => $message,
                'exception_class' => get_class($exception),
                'exception_message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            // Attempt to rollback by deleting the tenant
            try {
                \Log::warning('Attempting to rollback tenant creation', ['tenant_id' => $tenant->id]);
                DB::connection('central')->transaction(function () use ($tenant) {
                    $tenant->domains()->delete();
                    $tenant->delete();
                });
                \Log::info('Tenant rollback successful', ['tenant_id' => $tenant->id]);
            } catch (\Exception $rollbackException) {
                \Log::error('Tenant rollback failed', [
                    'tenant_id' => $tenant->id,
                    'error' => $rollbackException->getMessage(),
                ]);
            }
        } else {
            \Log::error('Tenant registration failed before tenant creation', [
                'message' => $message,
                'exception_class' => get_class($exception),
                'exception_message' => $exception->getMessage(),
            ]);
        }
    }
}
