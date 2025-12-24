<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Inertia\Inertia;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        // Check if tenant exists
        if (!tenancy()->initialized) {
            // Try to identify tenant by domain
            $host = $request->getHost();
            $subdomain = explode('.', $host)[0];
            
            $tenant = \App\Models\Tenant::where('id', $subdomain)->first();
            if (!$tenant) {
                abort(404, 'Store not found');
            }
            
            // Initialize tenancy
            tenancy()->initialize($tenant);
        }

        $products = Product::active()
            ->latest()
            ->paginate(12);

        $cartCount = 0;
        if (auth()->check()) {
            $cartCount = auth()->user()->carts()->sum('quantity') ?? 0;
        }

        return Inertia::render('Shop/Index', [
            'products' => $products,
            'auth' => [
                'user' => auth()->user(),
                'cartCount' => $cartCount,
            ],
        ]);
    }

    public function show(Product $product)
    {
        return Inertia::render('Shop/Show', [
            'product' => $product,
        ]);
    }
}
