<?php

namespace Tests;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

abstract class TenantTestCase extends TestCase
{
    use RefreshDatabase;

    /**
     * The test tenant instance.
     */
    protected ?Tenant $tenant = null;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Run tenant migrations on the test database
        $this->runTenantMigrations();

        // Create a test tenant for testing
        $this->tenant = $this->createTestTenant();
    }

    /**
     * Run the tenant migrations on the test database.
     */
    protected function runTenantMigrations(): void
    {
        Artisan::call('migrate', [
            '--database' => 'pgsql',
            '--path' => 'database/migrations/tenant',
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
