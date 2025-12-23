<?php

namespace App\Policies;

use App\Models\Cart;
use App\Models\User;

class CartPolicy
{
    /**
     * Determine if the user can view any carts.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the cart.
     */
    public function view(User $user, Cart $cart): bool
    {
        // User can only view their own cart
        return $cart->user_id === $user->id;
    }

    /**
     * Determine if the user can create carts.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the cart.
     */
    public function update(User $user, Cart $cart): bool
    {
        // User can only update their own cart
        return $cart->user_id === $user->id;
    }

    /**
     * Determine if the user can delete the cart.
     */
    public function delete(User $user, Cart $cart): bool
    {
        // User can only delete their own cart
        return $cart->user_id === $user->id;
    }

    /**
     * Determine if the user can restore the cart.
     */
    public function restore(User $user, Cart $cart): bool
    {
        return $cart->user_id === $user->id;
    }

    /**
     * Determine if the user can permanently delete the cart.
     */
    public function forceDelete(User $user, Cart $cart): bool
    {
        return $cart->user_id === $user->id;
    }
}
