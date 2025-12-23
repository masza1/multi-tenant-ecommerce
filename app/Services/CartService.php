<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Get or create a guest cart for the current session.
     */
    public function getOrCreateGuestCart(): Cart
    {
        $sessionId = Session::getId();

        $cart = Cart::where('session_id', $sessionId)
            ->where('status', 'active')
            ->first();

        if (!$cart) {
            $cart = Cart::create([
                'session_id' => $sessionId,
                'status' => 'active',
                'last_activity_at' => now(),
            ]);
        }

        return $cart;
    }

    /**
     * Get or create a user's cart.
     */
    public function getOrCreateUserCart(User $user): Cart
    {
        $cart = Cart::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (!$cart) {
            $cart = Cart::create([
                'user_id' => $user->id,
                'status' => 'active',
                'last_activity_at' => now(),
            ]);
        }

        return $cart;
    }

    /**
     * Get current cart (guest or user).
     */
    public function getCurrentCart(): ?Cart
    {
        if (auth()->check()) {
            return $this->getOrCreateUserCart(auth()->user());
        }

        return $this->getOrCreateGuestCart();
    }

    /**
     * Add product to cart.
     */
    public function addToCart(Product $product, int $quantity = 1, User $user = null): Cart
    {
        // Validate product exists and is active
        if (!$product->active) {
            throw new \Exception("Product is not available for purchase");
        }

        // Get appropriate cart
        if ($user) {
            $cart = $this->getOrCreateUserCart($user);
        } else {
            $cart = $this->getOrCreateGuestCart();
        }

        // Add item to cart
        $cart->addItem($product, $quantity);

        return $cart;
    }

    /**
     * Update cart item quantity.
     */
    public function updateCartItem($cartItemId, int $quantity): Cart
    {
        $item = \App\Models\CartItem::findOrFail($cartItemId);

        // Verify cart ownership
        if (!auth()->check() && $item->cart->session_id !== Session::getId()) {
            throw new \Exception("Unauthorized cart access");
        }

        if (auth()->check() && $item->cart->user_id !== auth()->id()) {
            throw new \Exception("Unauthorized cart access");
        }

        $item->cart->updateItemQuantity($item, $quantity);

        return $item->cart;
    }

    /**
     * Remove item from cart.
     */
    public function removeFromCart($cartItemId): Cart
    {
        $item = \App\Models\CartItem::findOrFail($cartItemId);

        // Verify cart ownership
        if (!auth()->check() && $item->cart->session_id !== Session::getId()) {
            throw new \Exception("Unauthorized cart access");
        }

        if (auth()->check() && $item->cart->user_id !== auth()->id()) {
            throw new \Exception("Unauthorized cart access");
        }

        $item->cart->removeItem($item);

        return $item->cart;
    }

    /**
     * Clear cart.
     */
    public function clearCart(Cart $cart): void
    {
        // Verify cart ownership
        if (!auth()->check() && $cart->session_id !== Session::getId()) {
            throw new \Exception("Unauthorized cart access");
        }

        if (auth()->check() && $cart->user_id !== auth()->id()) {
            throw new \Exception("Unauthorized cart access");
        }

        $cart->clear();
    }

    /**
     * Merge guest cart into user cart.
     * Called when user logs in.
     */
    public function mergeGuestCartToUser(User $user): void
    {
        $sessionId = Session::getId();

        // Get guest cart
        $guestCart = Cart::where('session_id', $sessionId)
            ->where('status', 'active')
            ->first();

        if (!$guestCart || $guestCart->isEmpty()) {
            return;
        }

        // Get or create user cart
        $userCart = $this->getOrCreateUserCart($user);

        // Merge items from guest cart to user cart
        foreach ($guestCart->items as $guestItem) {
            // Check if item already exists in user cart
            $existingItem = $userCart->items()
                ->where('product_id', $guestItem->product_id)
                ->first();

            if ($existingItem) {
                // Add guest quantity to existing user cart item
                $newQuantity = $existingItem->quantity + $guestItem->quantity;
                $userCart->updateItemQuantity($existingItem, $newQuantity);
            } else {
                // Move item to user cart
                $guestItem->update([
                    'cart_id' => $userCart->id,
                ]);
            }
        }

        // Mark guest cart as converted
        $guestCart->markAsConverted();

        // Recalculate user cart totals
        $userCart->calculateTotals();
    }

    /**
     * Get cart value for display.
     */
    public function getCartValue(): array
    {
        $cart = $this->getCurrentCart();

        if (!$cart) {
            return [
                'items' => 0,
                'subtotal' => 0,
                'tax' => 0,
                'shipping' => 0,
                'total' => 0,
            ];
        }

        return [
            'items' => $cart->item_count,
            'subtotal' => (float) $cart->subtotal,
            'tax' => (float) $cart->tax,
            'shipping' => (float) $cart->shipping,
            'total' => (float) $cart->total,
        ];
    }

    /**
     * Get abandoned carts (older than 24 hours).
     */
    public function getAbandonedCarts(): \Illuminate\Database\Eloquent\Collection
    {
        return Cart::where('status', 'active')
            ->where('last_activity_at', '<', now()->subDay())
            ->get();
    }

    /**
     * Mark old inactive carts as abandoned.
     */
    public function markAbandonedCarts(): void
    {
        Cart::where('status', 'active')
            ->where('last_activity_at', '<', now()->subDay())
            ->update(['status' => 'abandoned']);
    }
}
