<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\TenantRegisterController;
use Illuminate\Support\Facades\Route;

// Central domain routes (Landing page & Tenant Registration)
// These routes are available on central domains only (handled by PreventAccessFromCentralDomains middleware)
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::post('/register-tenant', [TenantRegisterController::class, 'store'])
    ->name('tenant.register');
