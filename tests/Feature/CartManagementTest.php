<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Tests\TenantTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartManagementTest extends TenantTestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createTestUser();
    }

    // =====================================================
    // CART VIEWING
    // =====================================================

    public function test_authenticated_user_can_view_cart()
    {
        $product = Product::factory()->create(['stock' => 10]);
        Cart::factory()->create(['user_id' => $this->user->id, 'product_id' => $product->id, 'quantity' => 2]);

        $response = $this->actingAs($this->user)->get('/cart');

        $response->assertStatus(200)->assertInertia(fn ($page) =>
            $page->component('Cart/Index')->has('cartItems')
        );
    }

    public function test_cart_shows_only_user_own_items()
    {
        $user2 = User::factory()->create();
        $product = Product::factory()->create();

        Cart::factory()->create(['user_id' => $this->user->id, 'product_id' => $product->id, 'quantity' => 2]);
        Cart::factory()->create(['user_id' => $user2->id, 'product_id' => $product->id, 'quantity' => 3]);

        $response = $this->actingAs($this->user)->get('/cart');
        $response->assertInertia(fn ($page) => $page->has('cartItems', 1));
    }

    // =====================================================
    // CART DATABASE OPERATIONS
    // =====================================================

    public function test_can_add_product_to_cart_database()
    {
        $product = Product::factory()->create(['stock' => 10]);

        Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 2
        ]);
    }

    public function test_can_update_cart_in_database()
    {
        $product = Product::factory()->create(['stock' => 20]);
        $cart = Cart::factory()->create(['user_id' => $this->user->id, 'product_id' => $product->id, 'quantity' => 5]);

        $cart->update(['quantity' => 10]);

        $this->assertEquals(10, $cart->fresh()->quantity);
    }

    public function test_can_delete_cart_from_database()
    {
        $product = Product::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $this->user->id, 'product_id' => $product->id]);

        $cart->delete();

        $this->assertFalse(Cart::where('id', $cart->id)->exists());
    }

    // =====================================================
    // AUTHORIZATION
    // =====================================================

    public function test_user_cannot_modify_other_user_cart()
    {
        $user2 = User::factory()->create();
        $product = Product::factory()->create();
        $cart = Cart::factory()->create(['user_id' => $user2->id, 'product_id' => $product->id, 'quantity' => 2]);

        // Policy check
        $this->assertFalse($this->user->can('update', $cart));
        $this->assertFalse($this->user->can('delete', $cart));
    }
}
