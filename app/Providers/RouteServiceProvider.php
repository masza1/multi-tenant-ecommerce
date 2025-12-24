<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class RouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerTenantRoutes();
    }

    protected function registerTenantRoutes(): void
    {
        // Register tenant routes with proper middleware
        Route::middleware([
            'web',
            PreventAccessFromCentralDomains::class,
            InitializeTenancyByDomain::class,
        ])->group(base_path('routes/tenant.php'));
    }
}
