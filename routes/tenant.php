<?php

declare(strict_types=1);

use App\Http\Controllers\Tenant\ShopController;
use App\Http\Controllers\Tenant\CartController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\Auth\AuthController;
use App\Http\Middleware\VerifyTenantDatabase;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
|
| These routes are wrapped in middleware applied by RouteServiceProvider:
| - web
| - InitializeTenancyByDomain
|
| VerifyTenantDatabase is applied at route level for additional safety.
|
*/

// ===== PUBLIC ROUTES (Guest) =====

// Shop Index - Product Catalog
Route::middleware([VerifyTenantDatabase::class])->get('/', [ShopController::class, 'index'])->name('shop.index');

// Storefront (Product Catalog) - Individual product page
Route::middleware([VerifyTenantDatabase::class])->group(function () {
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
            Route::get('/admin', [ProductController::class, 'index'])->name('admin.dashboard');
            Route::get('/admin/products/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('/admin/products', [ProductController::class, 'store'])->name('products.store');
            Route::get('/admin/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::patch('/admin/products/{product}', [ProductController::class, 'update'])->name('products.update');
            Route::delete('/admin/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        });
    });
});
