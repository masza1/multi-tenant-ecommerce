<?php

namespace Tests\Unit;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Tests\TenantTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartModelTest extends TenantTestCase
{
    use RefreshDatabase;

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    public function test_cart_belongs_to_user()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id
        ]);

        $this->assertTrue($cart->user->is($user));
    }

    public function test_cart_belongs_to_product()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id
        ]);

        $this->assertTrue($cart->product->is($product));
    }

    public function test_product_relationship_eager_loaded()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id
        ]);

        $cart = Cart::first();
        $this->assertTrue($cart->relationLoaded('product'));
    }

    // =====================================================
    // SUBTOTAL ACCESSOR
    // =====================================================

    public function test_subtotal_calculates_correctly()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100.00]);

        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 3
        ]);

        $this->assertEquals(300.00, $cart->subtotal);
    }

    public function test_subtotal_with_decimal_prices()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 19.99]);

        $cart = Cart::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $this->assertEquals(39.98, $cart->subtotal);
    }

    // =====================================================
    // FILLABLE & CASTS
    // =====================================================

    public function test_can_mass_assign_attributes()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $cart = Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 5
        ]);

        $this->assertEquals($user->id, $cart->user_id);
        $this->assertEquals($product->id, $cart->product_id);
        $this->assertEquals(5, $cart->quantity);
    }

    public function test_quantity_cast_to_integer()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $cart = Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => "5"
        ]);

        $this->assertIsInt($cart->quantity);
        $this->assertEquals(5, $cart->quantity);
    }

    // =====================================================
    // DATABASE CONSTRAINTS
    // =====================================================

    public function test_cart_has_timestamps()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $cart = Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $this->assertNotNull($cart->created_at);
        $this->assertNotNull($cart->updated_at);
    }
}
