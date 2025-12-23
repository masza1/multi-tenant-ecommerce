<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\ShopController;
use App\Http\Controllers\Tenant\CartController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\Auth\AuthController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    // ===== PUBLIC ROUTES (Guest) =====

    // Storefront (Product Catalog)
    Route::get('/', [ShopController::class, 'index'])->name('shop.index');
    Route::get('/products/{product}', [ShopController::class, 'show'])->name('shop.show');

    // Auth Routes
    Route::middleware('guest')->group(function () {
        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register']);
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
    });

    // ===== AUTHENTICATED ROUTES =====

    Route::middleware('auth')->group(function () {

        // Logout
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        // Shopping Cart
        Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
        Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
        Route::patch('/cart/{cart}', [CartController::class, 'update'])->name('cart.update');
        Route::delete('/cart/{cart}', [CartController::class, 'destroy'])->name('cart.destroy');

        // ===== ADMIN ONLY ROUTES =====

        Route::middleware('role:admin')->group(function () {
            Route::get('/admin', function () {
                return redirect()->route('products.index');
            })->name('admin.dashboard');

            Route::resource('products', ProductController::class);
        });
    });
});
