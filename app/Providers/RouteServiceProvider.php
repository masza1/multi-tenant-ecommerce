<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\InitializeTenancyByDomain;
use App\Http\Middleware\SetTenantConnectionForAuth;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerTenantRoutes();
    }

    protected function registerTenantRoutes(): void
    {
        // Register tenant routes with proper middleware
        // IMPORTANT: InitializeTenancyByDomain must run BEFORE 'web' so that
        // tenancy is initialized before auth middleware runs
        Route::middleware([
            InitializeTenancyByDomain::class,
            'web',
            SetTenantConnectionForAuth::class,
        ])->group(base_path('routes/tenant.php'));
    }
}

