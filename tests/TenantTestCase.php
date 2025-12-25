<?php

namespace Tests;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

abstract class TenantTestCase extends TestCase
{
    // Don't use RefreshDatabase - run migrations once and keep tables for debugging
    // use RefreshDatabase;

    /**
     * The test tenant instance.
     */
    protected static ?Tenant $staticTenant = null;
    protected ?Tenant $tenant = null;

    private static bool $migrationsRun = false;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Force test database configuration
        config([
            'database.default' => 'pgsql',
            'database.connections.pgsql.database' => env('DB_DATABASE', 'multitenant_test'),
        ]);

        // Override central connection to use test database
        config([
            'database.connections.central.database' => env('DB_DATABASE', 'multitenant_test'),
        ]);

        // Run migrations only once at the beginning of test suite
        if (!static::$migrationsRun) {
            $this->artisanMigrate();
            static::$migrationsRun = true;
        }

        // DON'T clean up data - keep all test data for inspection
        // $this->cleanupTestData();

        // Reuse the same tenant for all tests (avoid creating multiple databases)
        if (static::$staticTenant === null) {
            static::$staticTenant = $this->createTestTenant();
        }
        $this->tenant = static::$staticTenant;
    }

    /**
     * Clean up test data between tests while keeping tables.
     */
    protected function cleanupTestData(): void
    {
        try {
            // Truncate tables to clean data but keep structure
            DB::statement('TRUNCATE TABLE carts CASCADE');
            DB::statement('TRUNCATE TABLE products CASCADE');
            DB::statement('TRUNCATE TABLE users CASCADE');
            DB::statement('TRUNCATE TABLE tenants CASCADE');
        } catch (\Exception $e) {
            // Tables may not exist yet, skip cleanup
        }
    }

    /**
     * Run migrations properly for tests.
     * Uses the configured DB_DATABASE from phpunit.xml (multitenant_test)
     */
    protected function artisanMigrate(): void
    {
        // Ensure we're using the test database config
        config(['database.default' => 'pgsql']);

        // Run ONLY landlord migrations in central database
        // Tenant migrations will run separately for each tenant
        Artisan::call('migrate:fresh', [
            '--database' => 'pgsql',
            '--path' => 'database/migrations/landlord',
            '--force' => true,
        ]);
    }

    /**
     * Create a test tenant with a domain.
     * Note: This creates a tenant record in the central database for testing purposes.
     * In production, stancl/tenancy handles database creation automatically.
     */
    protected function createTestTenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Test Store ' . time(),
            'email' => 'test-' . time() . '@example.com',
        ]);
    }

    /**
     * Create an additional test tenant for isolation testing.
     */
    protected function createAnotherTestTenant(): Tenant
    {
        return Tenant::create([
            'name' => 'Another Store ' . time(),
            'email' => 'another-' . time() . '@example.com',
        ]);
    }

    /**
     * Get the current test tenant.
     */
    protected function getTestTenant(): ?Tenant
    {
        return $this->tenant;
    }

    /**
     * Create a test user.
     */
    protected function createTestUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'email' => 'user-' . time() . '@example.com',
        ], $attributes));
    }

    /**
     * Create another test user for isolation testing.
     */
    protected function createAnotherTestUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'email' => 'user2-' . time() . '@example.com',
        ], $attributes));
    }
}
