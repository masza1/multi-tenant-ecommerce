<?php

namespace Tests\Unit;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Policies\CartPolicy;
use Tests\TenantTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartPolicyTest extends TenantTestCase
{
    use RefreshDatabase;

    private CartPolicy $policy;
    private User $user;
    private User $anotherUser;
    private Cart $cart;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new CartPolicy();
        $this->user = User::factory()->create();
        $this->anotherUser = User::factory()->create();

        $product = Product::factory()->create();
        $this->cart = Cart::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $product->id
        ]);
    }

    // =====================================================
    // UPDATE POLICY
    // =====================================================

    public function test_user_can_update_own_cart()
    {
        $this->assertTrue($this->policy->update($this->user, $this->cart));
    }

    public function test_user_cannot_update_other_users_cart()
    {
        $this->assertFalse($this->policy->update($this->anotherUser, $this->cart));
    }

    // =====================================================
    // DELETE POLICY
    // =====================================================

    public function test_user_can_delete_own_cart()
    {
        $this->assertTrue($this->policy->delete($this->user, $this->cart));
    }

    public function test_user_cannot_delete_other_users_cart()
    {
        $this->assertFalse($this->policy->delete($this->anotherUser, $this->cart));
    }

    // =====================================================
    // VIEW POLICY
    // =====================================================

    public function test_user_can_view_own_cart()
    {
        $this->assertTrue($this->policy->view($this->user, $this->cart));
    }

    public function test_user_cannot_view_other_users_cart()
    {
        $this->assertFalse($this->policy->view($this->anotherUser, $this->cart));
    }

    // =====================================================
    // CREATE POLICY
    // =====================================================

    public function test_authenticated_user_can_create_cart()
    {
        $this->assertTrue($this->policy->create($this->user));
    }
}
