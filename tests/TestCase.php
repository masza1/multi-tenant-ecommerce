<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Run landlord migrations before tests
        if (!$this->hasMigrationsRun()) {
            Artisan::call('migrate:fresh', [
                '--path' => 'database/migrations/landlord',
                '--force' => true,
            ]);
        }
    }

    protected function hasMigrationsRun(): bool
    {
        try {
            return \DB::table('tenants')->exists();
        } catch (\Exception $e) {
            return false;
        }
    }
}
