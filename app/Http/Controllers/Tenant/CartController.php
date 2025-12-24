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
            ->get()
            ->map(fn($item) => [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'product' => $item->product,
                'subtotal' => $item->subtotal,
            ]);

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

            // Check stock - tidak boleh melebihi stock barang
            if ($validated['quantity'] > $product->stock) {
                return back()->with('error', __('messages.insufficient_stock'));
            }

            // Check if item already in cart
            $existingCart = Cart::where('user_id', auth()->id())
                ->where('product_id', $validated['product_id'])
                ->first();

            // If already in cart, check total quantity
            if ($existingCart) {
                $totalQuantity = $existingCart->quantity + $validated['quantity'];
                if ($totalQuantity > $product->stock) {
                    return back()->with('error', __('messages.cannot_exceed_stock'));
                }
            }

            // Update or create cart item in transaction
            DB::transaction(function () use ($validated) {
                $cart = Cart::where('user_id', auth()->id())
                    ->where('product_id', $validated['product_id'])
                    ->first();
                
                if ($cart) {
                    $cart->increment('quantity', $validated['quantity']);
                } else {
                    Cart::create([
                        'user_id' => auth()->id(),
                        'product_id' => $validated['product_id'],
                        'quantity' => $validated['quantity'],
                    ]);
                }
            });

            return redirect()->back()->with('success', __('messages.product_added_to_cart'));
        } catch (\Exception $e) {
            \Log::error('CartController::store() - Error', ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]);
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
                session()->flash('error', __('messages.insufficient_stock'));
                return back();
            }

            // Update in transaction
            DB::transaction(function () use ($cart, $validated) {
                $cart->update($validated);
            });

            session()->flash('success', __('messages.cart_updated'));
            return back();
        } catch (\Exception $e) {
            session()->flash('error', __('messages.something_went_wrong'));
            return back();
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

            session()->flash('success', __('messages.item_removed_from_cart'));
            return back();
        } catch (\Exception $e) {
            session()->flash('error', __('messages.something_went_wrong'));
            return back();
        }
    }
}
