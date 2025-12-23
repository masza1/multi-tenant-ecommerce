<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 10);
        $price = $this->faker->randomFloat(2, 10, 500);
        $subtotal = $quantity * $price;

        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $subtotal,
            'product_data' => [
                'id' => $this->faker->numberBetween(1, 10000),
                'name' => $this->faker->words(3, true),
                'sku' => 'SKU-' . $this->faker->numerify('######'),
                'price' => $price,
            ],
        ];
    }

    /**
     * Set the quantity for this item.
     */
    public function quantity(int $quantity): static
    {
        return $this->state(function (array $attributes) use ($quantity) {
            return [
                'quantity' => $quantity,
                'subtotal' => $quantity * $attributes['price'],
            ];
        });
    }

    /**
     * Set the price for this item.
     */
    public function price(float $price): static
    {
        return $this->state(function (array $attributes) use ($price) {
            return [
                'price' => $price,
                'subtotal' => $attributes['quantity'] * $price,
            ];
        });
    }

    /**
     * Associate with specific cart.
     */
    public function forCart(Cart $cart): static
    {
        return $this->state(function (array $attributes) use ($cart) {
            return [
                'cart_id' => $cart->id,
            ];
        });
    }

    /**
     * Associate with specific product.
     */
    public function forProduct(Product $product): static
    {
        return $this->state(function (array $attributes) use ($product) {
            return [
                'product_id' => $product->id,
                'price' => $product->price,
                'subtotal' => $attributes['quantity'] * $product->price,
            ];
        });
    }
}
