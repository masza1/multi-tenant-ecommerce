<?php

namespace App\Providers;

use App\Session\CentralDatabaseSessionHandler;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Register custom tenant-aware user provider
        // This ensures auth queries use the correct database based on tenancy
        \Auth::provider('tenant_eloquent', function ($app, array $config) {
            return new \App\Auth\TenantAwareEloquentUserProvider(
                $app['hash'],
                $config['model']
            );
        });

        // Register custom database session handler that always uses central connection
        // This prevents session save from querying tenant database
        $this->app['session']->extend('database', function ($app) {
            return new CentralDatabaseSessionHandler(
                $app['db']->connection('central'),
                config('session.table'),
                config('session.lifetime'),
                $app
            );
        });

        // Event listeners commented out - MergeGuestCart listener not implemented
        // Event::listen(
        //     Login::class,
        //     MergeGuestCart::class
        // );
    }
}

