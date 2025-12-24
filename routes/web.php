<?php

use Illuminate\Support\Facades\Route;

// Single unified route for all / requests
Route::get('/', [\App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::post('/register-tenant', [\App\Http\Controllers\TenantRegisterController::class, 'store'])
    ->name('tenant.register');
