<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cart>
 */
class CartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'session_id' => Str::uuid(),
            'subtotal' => 0,
            'tax' => 0,
            'shipping' => 0,
            'total' => 0,
            'item_count' => 0,
            'status' => 'active',
            'last_activity_at' => now(),
        ];
    }

    /**
     * Indicate the cart belongs to a user.
     */
    public function forUser(User $user = null): static
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'user_id' => $user?->id ?? User::factory(),
                'session_id' => null,
            ];
        });
    }

    /**
     * Indicate the cart is a guest cart.
     */
    public function guest(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'user_id' => null,
                'session_id' => Str::uuid(),
            ];
        });
    }

    /**
     * Indicate the cart is abandoned.
     */
    public function abandoned(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'abandoned',
                'last_activity_at' => now()->subDays(2),
            ];
        });
    }

    /**
     * Indicate the cart has been converted to an order.
     */
    public function converted(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'converted',
            ];
        });
    }

    /**
     * Set cart with specific totals.
     */
    public function withTotals(float $subtotal, float $tax = null, float $shipping = null): static
    {
        return $this->state(function (array $attributes) use ($subtotal, $tax, $shipping) {
            $tax = $tax ?? round($subtotal * 0.1, 2);
            $shipping = $shipping ?? ($subtotal >= 100 ? 0 : 10);
            $total = $subtotal + $tax + $shipping;

            return [
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => $shipping,
                'total' => $total,
            ];
        });
    }
}
