<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);
        $price = $this->faker->randomFloat(2, 10, 1000);
        $originalPrice = $this->faker->boolean(70) ? $this->faker->randomFloat(2, $price + 10, $price + 500) : null;

        return [
            'category_id' => Category::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraphs(3, true),
            'short_description' => $this->faker->sentence(10),
            'price' => $price,
            'original_price' => $originalPrice,
            'sku' => 'SKU-' . $this->faker->unique()->numerify('######'),
            'stock' => $this->faker->numberBetween(0, 1000),
            'low_stock_threshold' => 5,
            'active' => true,
            'views' => $this->faker->numberBetween(0, 1000),
        ];
    }

    /**
     * Indicate that the product should be out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }

    /**
     * Indicate that the product should be inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    /**
     * Indicate that the product should be on sale.
     */
    public function onSale(): static
    {
        return $this->state(fn (array $attributes) => [
            'original_price' => $attributes['price'] + 100,
        ]);
    }
}
