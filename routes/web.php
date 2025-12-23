<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\TenantRegisterController;
use Illuminate\Support\Facades\Route;

// Landing Page (Marketing Site)
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Tenant Registration (Onboarding)
Route::post('/register-tenant', [TenantRegisterController::class, 'store'])
    ->name('tenant.register');
