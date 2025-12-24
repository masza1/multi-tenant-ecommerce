<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = Cart::where('user_id', auth()->id())
            ->with('product')
            ->get();

        $total = $cartItems->sum('subtotal');

        return Inertia::render('Cart/Index', [
            'cartItems' => $cartItems,
            'total' => $total,
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'product_id' => ['required', 'exists:products,id'],
                'quantity' => ['required', 'integer', 'min:1'],
            ]);

            $product = Product::findOrFail($validated['product_id']);

            // Check stock
            if ($product->stock < $validated['quantity']) {
                return back()->with('error', __('messages.insufficient_stock'));
            }

            // Update or create cart item in transaction
            DB::transaction(function () use ($validated) {
                Cart::updateOrCreate(
                    [
                        'user_id' => auth()->id(),
                        'product_id' => $validated['product_id'],
                    ],
                    [
                        'quantity' => DB::raw("quantity + {$validated['quantity']}")
                    ]
                );
            });

            return back()->with('success', __('messages.product_added_to_cart'));
        } catch (\Exception $e) {
            return back()->with('error', __('messages.something_went_wrong'));
        }
    }

    public function update(Request $request, Cart $cart)
    {
        try {
            $this->authorize('update', $cart);

            $validated = $request->validate([
                'quantity' => ['required', 'integer', 'min:1'],
            ]);

            // Check stock
            if ($cart->product->stock < $validated['quantity']) {
                return back()->with('error', __('messages.insufficient_stock'));
            }

            // Update in transaction
            DB::transaction(function () use ($cart, $validated) {
                $cart->update($validated);
            });

            return back()->with('success', __('messages.cart_updated'));
        } catch (\Exception $e) {
            return back()->with('error', __('messages.something_went_wrong'));
        }
    }

    public function destroy(Cart $cart)
    {
        try {
            $this->authorize('delete', $cart);

            // Delete in transaction
            DB::transaction(function () use ($cart) {
                $cart->delete();
            });

            return back()->with('success', __('messages.item_removed_from_cart'));
        } catch (\Exception $e) {
            return back()->with('error', __('messages.something_went_wrong'));
        }
    }
}
