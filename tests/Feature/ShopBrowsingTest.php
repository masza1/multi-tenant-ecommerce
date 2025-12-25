<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Tests\TenantTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShopBrowsingTest extends TenantTestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createTestUser();
    }

    public function test_shop_page_accessible()
    {
        Product::factory()->create(['is_active' => true]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_shop_page_pagination()
    {
        Product::factory()->count(15)->create(['is_active' => true]);

        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_cart_count_for_authenticated_user()
    {
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        Cart::factory()->create(['user_id' => $this->user->id, 'product_id' => $product1->id, 'quantity' => 2]);
        Cart::factory()->create(['user_id' => $this->user->id, 'product_id' => $product2->id, 'quantity' => 3]);

        $response = $this->actingAs($this->user)->get('/');

        $response->assertStatus(200)->assertInertia(fn ($page) =>
            $page->has('auth')
        );
    }
}
