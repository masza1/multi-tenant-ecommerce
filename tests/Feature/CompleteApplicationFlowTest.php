<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Complete Application Flow Test
 *
 * Tests the entire user journey from tenant registration through checkout
 */
class CompleteApplicationFlowTest extends TestCase
{
    private $tenant;
    private $admin;
    private $customer;

    protected function setUp(): void
    {
        parent::setUp();

        // Clean up old test tenants
        Tenant::where('id', 'like', 'flow-test%')->delete();

        // Create and setup tenant with unique ID
        $uniqueId = 'flow-test-' . time();
        $this->tenant = Tenant::create([
            'id' => $uniqueId,
            'name' => 'Flow Test Store',
            'owner_email' => 'owner@flowtest.test',
        ]);
        $this->tenant->domains()->create(['domain' => 'flowtest.localhost']);
        Artisan::call('tenants:migrate', ['--tenants' => [$uniqueId]]);
    }

    // ============================================
    // TENANT REGISTRATION & SETUP TESTS
    // ============================================

    public function test_tenant_registration_creates_store()
    {
        $response = $this->post('/register-tenant', [
            'store_name' => 'New Test Store',
            'subdomain' => 'new-test-store',
            'email' => 'owner@newtest.local',
        ]);

        $this->assertTrue(Tenant::where('name', 'New Test Store')->exists());
    }

    // ============================================
    // USER REGISTRATION & AUTHENTICATION TESTS
    // ============================================

    public function test_first_user_registration_becomes_admin()
    {
        tenancy()->initialize($this->tenant);

        $response = $this->post(route('register'), [
            'name' => 'Store Owner',
            'email' => 'owner@flowtest.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'owner@flowtest.test')->first();
        $this->assertEquals('admin', $user->role);

        tenancy()->end();
    }

    public function test_second_user_registration_becomes_customer()
    {
        tenancy()->initialize($this->tenant);

        // Create admin
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@flowtest.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Register second user
        $response = $this->post(route('register'), [
            'name' => 'Customer User',
            'email' => 'customer@flowtest.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $user = User::where('email', 'customer@flowtest.test')->first();
        $this->assertEquals('customer', $user->role);

        tenancy()->end();
    }

    public function test_user_login_and_logout()
    {
        tenancy()->initialize($this->tenant);

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@flowtest.test',
            'password' => bcrypt('password123'),
            'role' => 'customer',
        ]);

        // Login
        $response = $this->post(route('login'), [
            'email' => 'test@flowtest.test',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);

        // Logout
        $this->post(route('logout'));
        $this->assertGuest();

        tenancy()->end();
    }

    // ============================================
    // ADMIN PRODUCT MANAGEMENT TESTS
    // ============================================

    public function test_admin_creates_product()
    {
        tenancy()->initialize($this->tenant);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@flowtest.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin);
        $response = $this->post(route('products.store'), [
            'name' => 'Test Product',
            'description' => 'A test product',
            'price' => 500000,
            'stock' => 100,
            'is_active' => true,
        ]);

        $this->assertTrue(Product::where('name', 'Test Product')->exists());

        tenancy()->end();
    }

    public function test_admin_updates_product()
    {
        tenancy()->initialize($this->tenant);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@flowtest.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $product = Product::create([
            'name' => 'Original Name',
            'price' => 100000,
            'stock' => 50,
            'is_active' => true,
        ]);

        $this->actingAs($admin);
        $response = $this->patch(route('products.update', $product->id), [
            'name' => 'Updated Name',
            'price' => 150000,
            'stock' => 75,
            'is_active' => true,
        ]);

        $product->refresh();
        $this->assertEquals('Updated Name', $product->name);
        $this->assertEquals(150000, $product->price);

        tenancy()->end();
    }

    public function test_admin_deletes_product()
    {
        tenancy()->initialize($this->tenant);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@flowtest.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $product = Product::create([
            'name' => 'Product to Delete',
            'price' => 100000,
            'stock' => 50,
            'is_active' => true,
        ]);

        $this->actingAs($admin);
        $this->delete(route('products.destroy', $product->id));

        $this->assertNull(Product::find($product->id));

        tenancy()->end();
    }

    // ============================================
    // CUSTOMER SHOP BROWSING TESTS
    // ============================================

    public function test_customer_browses_product_catalog()
    {
        tenancy()->initialize($this->tenant);

        // Create products
        Product::create([
            'name' => 'Product 1',
            'price' => 100000,
            'stock' => 10,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Product 2',
            'price' => 200000,
            'stock' => 20,
            'is_active' => true,
        ]);

        $response = $this->get(route('shop.index'));
        $response->assertSee('Product 1');
        $response->assertSee('Product 2');

        tenancy()->end();
    }

    public function test_only_active_products_shown_in_shop()
    {
        tenancy()->initialize($this->tenant);

        Product::create([
            'name' => 'Active Product',
            'price' => 100000,
            'stock' => 10,
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Inactive Product',
            'price' => 200000,
            'stock' => 20,
            'is_active' => false,
        ]);

        $response = $this->get(route('shop.index'));
        $response->assertSee('Active Product');
        $response->assertDontSee('Inactive Product');

        tenancy()->end();
    }

    public function test_out_of_stock_products_show_unavailable()
    {
        tenancy()->initialize($this->tenant);

        Product::create([
            'name' => 'Out of Stock',
            'price' => 100000,
            'stock' => 0,
            'is_active' => true,
        ]);

        $response = $this->get(route('shop.index'));
        $response->assertSee('Out of Stock');
        $response->assertSee('Habis'); // Out of stock badge

        tenancy()->end();
    }

    // ============================================
    // SHOPPING CART TESTS
    // ============================================

    public function test_customer_adds_product_to_cart()
    {
        tenancy()->initialize($this->tenant);

        $customer = User::create([
            'name' => 'Customer',
            'email' => 'customer@flowtest.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'price' => 100000,
            'stock' => 50,
            'is_active' => true,
        ]);

        $this->actingAs($customer);
        $response = $this->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $cartItem = Cart::where('user_id', $customer->id)
            ->where('product_id', $product->id)
            ->first();

        $this->assertNotNull($cartItem);
        $this->assertEquals(2, $cartItem->quantity);

        tenancy()->end();
    }

    public function test_customer_cannot_exceed_stock_when_adding_to_cart()
    {
        tenancy()->initialize($this->tenant);

        $customer = User::create([
            'name' => 'Customer',
            'email' => 'customer@flowtest.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $product = Product::create([
            'name' => 'Limited Product',
            'price' => 100000,
            'stock' => 5,
            'is_active' => true,
        ]);

        $this->actingAs($customer);
        $response = $this->post(route('cart.store'), [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $response->assertSessionHas('error');
        $this->assertEquals(0, Cart::where('user_id', $customer->id)->count());

        tenancy()->end();
    }

    public function test_customer_views_cart_with_correct_total()
    {
        tenancy()->initialize($this->tenant);

        $customer = User::create([
            'name' => 'Customer',
            'email' => 'customer@flowtest.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $product1 = Product::create([
            'name' => 'Product 1',
            'price' => 100000,
            'stock' => 50,
            'is_active' => true,
        ]);

        $product2 = Product::create([
            'name' => 'Product 2',
            'price' => 200000,
            'stock' => 50,
            'is_active' => true,
        ]);

        Cart::create([
            'user_id' => $customer->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);

        Cart::create([
            'user_id' => $customer->id,
            'product_id' => $product2->id,
            'quantity' => 1,
        ]);

        $this->actingAs($customer);
        $response = $this->get(route('cart.index'));

        $response->assertSee('Product 1');
        $response->assertSee('Product 2');
        // Total: 2*100000 + 1*200000 = 400000
        $response->assertSee('400000');

        tenancy()->end();
    }

    public function test_customer_updates_cart_quantity()
    {
        tenancy()->initialize($this->tenant);

        $customer = User::create([
            'name' => 'Customer',
            'email' => 'customer@flowtest.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $product = Product::create([
            'name' => 'Product',
            'price' => 100000,
            'stock' => 50,
            'is_active' => true,
        ]);

        $cartItem = Cart::create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->actingAs($customer);
        $this->patch(route('cart.update', $cartItem->id), [
            'quantity' => 5,
        ]);

        $cartItem->refresh();
        $this->assertEquals(5, $cartItem->quantity);

        tenancy()->end();
    }

    public function test_customer_removes_item_from_cart()
    {
        tenancy()->initialize($this->tenant);

        $customer = User::create([
            'name' => 'Customer',
            'email' => 'customer@flowtest.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $product = Product::create([
            'name' => 'Product',
            'price' => 100000,
            'stock' => 50,
            'is_active' => true,
        ]);

        $cartItem = Cart::create([
            'user_id' => $customer->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->actingAs($customer);
        $this->delete(route('cart.destroy', $cartItem->id));

        $this->assertNull(Cart::find($cartItem->id));

        tenancy()->end();
    }

    // ============================================
    // ROLE-BASED ACCESS CONTROL TESTS
    // ============================================

    public function test_customer_cannot_access_admin_routes()
    {
        tenancy()->initialize($this->tenant);

        $customer = User::create([
            'name' => 'Customer',
            'email' => 'customer@flowtest.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $this->actingAs($customer);

        $this->get(route('products.index'))->assertStatus(403);
        $this->get(route('products.create'))->assertStatus(403);

        tenancy()->end();
    }

    public function test_admin_can_access_admin_routes()
    {
        tenancy()->initialize($this->tenant);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@flowtest.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $this->actingAs($admin);

        $this->get(route('products.index'))->assertStatus(200);
        $this->get(route('products.create'))->assertStatus(200);

        tenancy()->end();
    }

    // ============================================
    // TENANT ISOLATION TESTS
    // ============================================

    public function test_user_from_different_tenant_cannot_login()
    {
        // Create user in first tenant
        tenancy()->initialize($this->tenant);

        $user = User::create([
            'name' => 'User in Tenant A',
            'email' => 'user@tenant-a.test',
            'password' => bcrypt('password123'),
            'role' => 'customer',
        ]);

        tenancy()->end();

        // Create second tenant
        $tenant2 = Tenant::create([
            'id' => 'flow-test-store-2',
            'name' => 'Second Store',
            'owner_email' => 'owner2@flowtest.test',
        ]);
        $tenant2->domains()->create(['domain' => 'flowtest2.localhost']);
        Artisan::call('tenants:migrate', ['--tenants' => ['flow-test-store-2']]);

        // Try to login in second tenant
        tenancy()->initialize($tenant2);

        $response = $this->post(route('login'), [
            'email' => 'user@tenant-a.test',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        tenancy()->end();
    }
}
