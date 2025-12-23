<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Tests\TenantTestCase;

class CartTest extends TenantTestCase
{
    protected CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = new CartService();
    }

    /**
     * Test guest can add product to cart.
     */
    public function test_guest_can_add_product_to_cart(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create(['price' => 50, 'stock' => 100, 'active' => true]);

        $response = $this->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('cart.item_count', 2);
        $response->assertJsonPath('cart.subtotal', 100);
    }

    /**
     * Test user can add product to cart.
     */
    public function test_user_can_add_product_to_cart(): void
    {
        $user = $this->createTestUser();
        $category = Category::factory()->create();
        $product = Product::factory()->for($category)->create(['price' => 75, 'stock' => 50, 'active' => true]);

        $response = $this->actingAs($user)->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('cart.subtotal', 75);

        // Verify cart in database
        $this->assertDatabaseHas('carts', [
            'user_id' => $user->id,
            'status' => 'active',
        ]);
    }

    /**
     * Test cannot add product with insufficient stock.
     */
    public function test_cannot_add_product_with_insufficient_stock(): void
    {
        $product = Product::factory()->create(['price' => 50, 'stock' => 5, 'active' => true]);

        $response = $this->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('success', false);
    }

    /**
     * Test updating cart item quantity.
     */
    public function test_can_update_cart_item_quantity(): void
    {
        $user = $this->createTestUser();
        $product = Product::factory()->create(['price' => 50, 'stock' => 100, 'active' => true]);

        // Add to cart
        $this->actingAs($user)->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $item = CartItem::where('product_id', $product->id)->first();

        // Update quantity
        $response = $this->actingAs($user)->putJson("/cart-item/{$item->id}", [
            'quantity' => 5,
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('cart.item_count', 5);
        $response->assertJsonPath('cart.subtotal', 250);
    }

    /**
     * Test removing item from cart.
     */
    public function test_can_remove_item_from_cart(): void
    {
        $user = $this->createTestUser();
        $product = Product::factory()->create(['price' => 50, 'stock' => 100, 'active' => true]);

        // Add to cart
        $this->actingAs($user)->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $item = CartItem::where('product_id', $product->id)->first();

        // Remove item
        $response = $this->actingAs($user)->deleteJson("/cart-item/{$item->id}");

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('cart.item_count', 0);
    }

    /**
     * Test clearing entire cart.
     */
    public function test_can_clear_entire_cart(): void
    {
        $user = $this->createTestUser();
        $product = Product::factory()->create(['price' => 50, 'stock' => 100, 'active' => true]);

        // Add multiple items
        $this->actingAs($user)->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        // Clear cart
        $response = $this->actingAs($user)->post('/cart/clear');

        $response->assertRedirect();

        // Verify cart cleared
        $cart = Cart::where('user_id', $user->id)->first();
        $this->assertEquals(0, $cart->item_count);
    }

    /**
     * Test can get cart via API endpoint.
     */
    public function test_can_get_cart_via_api(): void
    {
        $user = $this->createTestUser();
        $product = Product::factory()->create(['price' => 100, 'stock' => 50, 'active' => true]);

        // Add item
        $this->actingAs($user)->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Get cart via API
        $response = $this->actingAs($user)->getJson('/api/cart');

        $response->assertOk();
        $response->assertJsonPath('cart.item_count', 2);
    }

    /**
     * Test cart totals calculation.
     */
    public function test_cart_totals_are_calculated_correctly(): void
    {
        $user = $this->createTestUser();
        $product = Product::factory()->create(['price' => 100, 'stock' => 50, 'active' => true]);

        $response = $this->actingAs($user)->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Subtotal: 100, Tax: 10 (10%), Shipping: 0 (free over $100), Total: 110
        $response->assertJsonPath('cart.subtotal', 100);
        $response->assertJsonPath('cart.tax', 10);
        $response->assertJsonPath('cart.shipping', 0);
        $response->assertJsonPath('cart.total', 110);
    }

    /**
     * Test shipping cost calculation.
     */
    public function test_shipping_is_calculated_correctly(): void
    {
        $user = $this->createTestUser();
        $product = Product::factory()->create(['price' => 50, 'stock' => 50, 'active' => true]);

        $response = $this->actingAs($user)->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Subtotal: 50, Tax: 5 (10%), Shipping: 10 (under $100), Total: 65
        $response->assertJsonPath('cart.subtotal', 50);
        $response->assertJsonPath('cart.shipping', 10);
        $response->assertJsonPath('cart.total', 65);
    }

    /**
     * Test free shipping over $100.
     */
    public function test_free_shipping_over_100(): void
    {
        $user = $this->createTestUser();
        $product = Product::factory()->create(['price' => 150, 'stock' => 50, 'active' => true]);

        $response = $this->actingAs($user)->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        // Subtotal: 150, Tax: 15 (10%), Shipping: 0 (free over $100), Total: 165
        $response->assertJsonPath('cart.shipping', 0);
        $response->assertJsonPath('cart.total', 165);
    }

    /**
     * Test cart merge on login (guest to user).
     */
    public function test_guest_cart_merges_to_user_cart_on_login(): void
    {
        // Guest adds item
        $product = Product::factory()->create(['price' => 50, 'stock' => 100, 'active' => true]);

        $this->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // User registers/logs in
        $user = $this->createTestUser();

        // Merge guest cart via service
        $this->cartService->mergeGuestCartToUser($user);

        // Verify cart was merged
        $userCart = Cart::where('user_id', $user->id)->first();
        $this->assertNotNull($userCart);
        $this->assertCount(1, $userCart->items);
    }

    /**
     * Test combining quantities when merging guest and user carts.
     */
    public function test_cart_merge_combines_duplicate_item_quantities(): void
    {
        $product = Product::factory()->create(['price' => 50, 'stock' => 100, 'active' => true]);
        $user = $this->createTestUser();

        // Guest adds item (before login)
        $this->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // User already has item in cart
        $userCart = Cart::factory()->forUser($user)->create();
        $userCart->addItem($product, 3);

        // Merge guest cart
        $this->cartService->mergeGuestCartToUser($user);

        // Verify combined quantity
        $item = CartItem::where('cart_id', $userCart->id)
            ->where('product_id', $product->id)
            ->first();

        // Should be 3 from user cart + 2 from guest = 5
        $this->assertEquals(5, $item->quantity);
    }

    /**
     * Test out of stock items detection.
     */
    public function test_can_detect_out_of_stock_items(): void
    {
        $user = $this->createTestUser();
        $product = Product::factory()->create(['price' => 50, 'stock' => 10, 'active' => true]);

        // Add to cart
        $this->actingAs($user)->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        // Reduce stock
        $product->update(['stock' => 3]);

        // Get cart
        $response = $this->actingAs($user)->getJson('/api/cart');

        $response->assertJsonPath('cart.has_out_of_stock', true);
    }

    /**
     * Test cart item discount calculation.
     */
    public function test_cart_item_discount_calculation(): void
    {
        $user = $this->createTestUser();
        $product = Product::factory()->create([
            'price' => 75,
            'original_price' => 100,
            'stock' => 50,
            'active' => true,
        ]);

        $this->actingAs($user)->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->getJson('/api/cart');

        // Discount: (100 - 75) * 1 = 25
        $response->assertJsonPath('cart.items.0.discount', 25);
    }

    /**
     * Test guest can retrieve empty cart.
     */
    public function test_guest_can_retrieve_empty_cart(): void
    {
        $product = Product::factory()->create(['price' => 50, 'stock' => 100, 'active' => true]);

        // Add to cart
        $response1 = $this->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $itemCount = $response1->json('cart.item_count');

        // Cart should have the item
        $this->assertEquals(2, $itemCount);
    }

    /**
     * Test cannot update quantity above stock.
     */
    public function test_cannot_update_quantity_above_stock(): void
    {
        $user = $this->createTestUser();
        $product = Product::factory()->create(['price' => 50, 'stock' => 10, 'active' => true]);

        // Add to cart
        $this->actingAs($user)->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $item = CartItem::where('product_id', $product->id)->first();

        // Try to update to quantity above stock
        $response = $this->actingAs($user)->putJson("/cart-item/{$item->id}", [
            'quantity' => 20,
        ]);

        $response->assertStatus(400);
        $response->assertJsonPath('success', false);
    }

    /**
     * Test cart status tracking.
     */
    public function test_cart_has_status_tracking(): void
    {
        $user = $this->createTestUser();
        $product = Product::factory()->create(['price' => 50, 'stock' => 100, 'active' => true]);

        // Add to cart
        $this->actingAs($user)->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $cart = Cart::where('user_id', $user->id)->first();

        // Verify status is active
        $this->assertEquals('active', $cart->status);
        $this->assertNotNull($cart->last_activity_at);
    }

    /**
     * Test adding same product twice combines quantities.
     */
    public function test_adding_same_product_twice_combines_quantities(): void
    {
        $user = $this->createTestUser();
        $product = Product::factory()->create(['price' => 50, 'stock' => 100, 'active' => true]);

        // Add first time
        $this->actingAs($user)->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Add second time
        $response = $this->actingAs($user)->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        // Should have 5 total
        $response->assertJsonPath('cart.item_count', 5);
        $response->assertJsonPath('cart.subtotal', 250);
    }

    /**
     * Test cart is isolated between users.
     */
    public function test_cart_is_isolated_between_users(): void
    {
        $user1 = $this->createTestUser();
        $user2 = $this->createAnotherTestUser();
        $product = Product::factory()->create(['price' => 50, 'stock' => 100, 'active' => true]);

        // User1 adds to cart
        $this->actingAs($user1)->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        // User2 adds to cart
        $this->actingAs($user2)->postJson('/cart', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        // Verify user1 still has 5
        $response1 = $this->actingAs($user1)->getJson('/api/cart');
        $this->assertEquals(5, $response1->json('cart.item_count'));

        // Verify user2 has 2
        $response2 = $this->actingAs($user2)->getJson('/api/cart');
        $this->assertEquals(2, $response2->json('cart.item_count'));
    }
}
