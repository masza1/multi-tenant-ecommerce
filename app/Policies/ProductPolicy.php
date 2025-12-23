<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Determine if the user can view any products.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the product.
     */
    public function view(User $user, Product $product): bool
    {
        // Users can view products in their own tenant
        return true;
    }

    /**
     * Determine if the user can create products.
     */
    public function create(User $user): bool
    {
        // Check if user has admin role or permission
        return $user->hasRole('admin') || $user->hasPermissionTo('create products');
    }

    /**
     * Determine if the user can update the product.
     */
    public function update(User $user, Product $product): bool
    {
        // Check if user has admin role or permission
        return $user->hasRole('admin') || $user->hasPermissionTo('update products');
    }

    /**
     * Determine if the user can delete the product.
     */
    public function delete(User $user, Product $product): bool
    {
        // Check if user has admin role or permission
        return $user->hasRole('admin') || $user->hasPermissionTo('delete products');
    }

    /**
     * Determine if the user can restore the product.
     */
    public function restore(User $user, Product $product): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('restore products');
    }

    /**
     * Determine if the user can permanently delete the product.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return $user->hasRole('admin') || $user->hasPermissionTo('force delete products');
    }
}
