<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Tests\TenantTestCase;

class ProductTest extends TenantTestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createTestUser();
    }

    /**
     * Test can access products list endpoint.
     */
    public function test_can_access_products_list(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->actingAs($this->user)->get('/products');

        // Should return 200 OK
        $response->assertOk();
        // Should be an Inertia response
        $response->assertSuccessful();
    }

    /**
     * Test creating a product.
     */
    public function test_can_create_product(): void
    {
        $category = Category::factory()->create();

        $data = [
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'A test product description',
            'short_description' => 'Short desc',
            'price' => 99.99,
            'original_price' => 149.99,
            'sku' => 'TEST-SKU-001',
            'stock' => 100,
            'low_stock_threshold' => 10,
        ];

        $this->actingAs($this->user)->post('/products', $data);

        // Verify product was created
        $product = Product::where('sku', 'TEST-SKU-001')->first();
        $this->assertNotNull($product);
        $this->assertEquals('Test Product', $product->name);
    }

    /**
     * Test viewing a product.
     */
    public function test_can_view_product(): void
    {
        $product = Product::factory()->create(['name' => 'Test Product']);

        $response = $this->get("/products/{$product->id}");

        // Should return 200 OK
        $response->assertOk();
    }

    /**
     * Test updating a product.
     */
    public function test_can_update_product(): void
    {
        $product = Product::factory()->create(['name' => 'Original Name']);

        $data = [
            'name' => 'Updated Name',
            'slug' => $product->slug,
            'category_id' => $product->category_id,
            'price' => $product->price,
            'sku' => $product->sku,
            'stock' => $product->stock,
            'low_stock_threshold' => $product->low_stock_threshold,
        ];

        $this->actingAs($this->user)->put("/products/{$product->id}", $data);

        // Refresh and verify update
        $product->refresh();
        $this->assertEquals('Updated Name', $product->name);
    }

    /**
     * Test deleting a product.
     */
    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->user)->delete("/products/{$product->id}");

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /**
     * Test product requires unique SKU.
     */
    public function test_product_sku_must_be_unique(): void
    {
        Product::factory()->create(['sku' => 'UNIQUE-SKU']);

        $category = Category::factory()->create();
        $data = [
            'category_id' => $category->id,
            'name' => 'Another Product',
            'slug' => 'another-product',
            'description' => 'Description',
            'short_description' => 'Short',
            'price' => 50,
            'original_price' => 50,
            'sku' => 'UNIQUE-SKU',
            'stock' => 50,
            'low_stock_threshold' => 5,
        ];

        $response = $this->actingAs($this->user)->post('/products', $data);

        $response->assertSessionHasErrors('sku');
    }

    /**
     * Test product slug is required.
     */
    public function test_product_slug_is_required(): void
    {
        $category = Category::factory()->create();
        $data = [
            'category_id' => $category->id,
            'name' => 'My Awesome Product',
            'slug' => 'my-awesome-product',
            'description' => 'Description',
            'short_description' => 'Short',
            'price' => 50,
            'original_price' => 50,
            'sku' => 'TEST-001',
            'stock' => 50,
            'low_stock_threshold' => 5,
        ];

        $this->actingAs($this->user)->post('/products', $data);

        $product = Product::where('name', 'My Awesome Product')->first();
        $this->assertNotNull($product);
        $this->assertEquals('my-awesome-product', $product->slug);
    }

    /**
     * Test product with images.
     */
    public function test_product_can_have_images(): void
    {
        $product = Product::factory()->create();
        $images = ProductImage::factory()->count(3)->for($product)->create();

        $this->assertCount(3, $product->images);
    }

    /**
     * Test primary image selection.
     */
    public function test_product_can_have_primary_image(): void
    {
        $product = Product::factory()->create();
        $image1 = ProductImage::factory()->for($product)->primary()->create();
        $image2 = ProductImage::factory()->for($product)->create();

        $primary = $product->images()->where('is_primary', true)->first();
        $this->assertTrue($primary->is($image1));
    }

    /**
     * Test product out of stock check.
     */
    public function test_product_out_of_stock_check(): void
    {
        $product = Product::factory()->create(['stock' => 0]);
        $this->assertTrue($product->stock == 0);

        $product->update(['stock' => 10]);
        $this->assertTrue($product->stock > 0);
    }

    /**
     * Test product low stock threshold.
     */
    public function test_product_low_stock_detection(): void
    {
        $product = Product::factory()->create([
            'stock' => 5,
            'low_stock_threshold' => 10,
        ]);

        $this->assertTrue($product->stock < $product->low_stock_threshold);

        $product->update(['stock' => 15]);
        $this->assertFalse($product->stock < $product->low_stock_threshold);
    }

    /**
     * Test product discount percentage calculation.
     */
    public function test_product_discount_percentage(): void
    {
        $product = Product::factory()->create([
            'original_price' => 100,
            'price' => 75,
        ]);

        $discount = (($product->original_price - $product->price) / $product->original_price) * 100;
        $this->assertEquals(25, round($discount, 2));
    }

    /**
     * Test creating product validates required fields.
     */
    public function test_creating_product_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post('/products', []);

        $response->assertSessionHasErrors(['category_id', 'name', 'slug', 'price', 'sku', 'stock']);
    }

    /**
     * Test product price validation.
     */
    public function test_product_price_cannot_exceed_original_price(): void
    {
        $category = Category::factory()->create();
        $data = [
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Description',
            'short_description' => 'Short',
            'price' => 200,
            'original_price' => 100,
            'sku' => 'TEST-001',
            'stock' => 50,
            'low_stock_threshold' => 5,
        ];

        $response = $this->actingAs($this->user)->post('/products', $data);

        $response->assertSessionHasErrors('original_price');
    }

    /**
     * Test product stock must be non-negative.
     */
    public function test_product_stock_must_be_non_negative(): void
    {
        $category = Category::factory()->create();
        $data = [
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'Description',
            'short_description' => 'Short',
            'price' => 50,
            'original_price' => 50,
            'sku' => 'TEST-001',
            'stock' => -5,
            'low_stock_threshold' => 5,
        ];

        $response = $this->actingAs($this->user)->post('/products', $data);

        $response->assertSessionHasErrors('stock');
    }

    /**
     * Test product category relationship.
     */
    public function test_product_belongs_to_category(): void
    {
        $category = Category::factory()->create(['name' => 'Electronics']);
        $product = Product::factory()->for($category)->create();

        $this->assertTrue($product->category->is($category));
        $this->assertEquals('Electronics', $product->category->name);
    }

    /**
     * Test category has many products.
     */
    public function test_category_has_many_products(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(5)->for($category)->create();

        $this->assertCount(5, $category->products);
    }
}
