<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    private string $token;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->token = $user->createToken('test')->plainTextToken;
        $this->category = Category::factory()->create();
    }

    public function test_list_products(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->withToken($this->token)->getJson('/api/products');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'total', 'current_page']);
    }

    public function test_create_product(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/products', [
            'name'        => 'Laptop Test',
            'sku'         => 'TST-001',
            'price'       => 999.99,
            'category_id' => $this->category->id,
            'stock'       => 10,
            'tax_rate'    => 15,
        ]);

        $response->assertStatus(201)
                 ->assertJson(['name' => 'Laptop Test', 'sku' => 'TST-001']);
    }

    public function test_create_product_requires_sku_uniqueness(): void
    {
        Product::factory()->create(['sku' => 'UNIQUE-SKU']);

        $response = $this->withToken($this->token)->postJson('/api/products', [
            'name'        => 'Otro Producto',
            'sku'         => 'UNIQUE-SKU',
            'price'       => 100,
            'category_id' => $this->category->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_show_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->withToken($this->token)->getJson("/api/products/{$product->id}");

        $response->assertStatus(200)
                 ->assertJson(['id' => $product->id]);
    }

    public function test_update_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->withToken($this->token)->putJson("/api/products/{$product->id}", [
            'name'  => 'Producto Actualizado',
            'price' => 150.00,
        ]);

        $response->assertStatus(200)
                 ->assertJson(['name' => 'Producto Actualizado']);
    }

    public function test_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->withToken($this->token)->deleteJson("/api/products/{$product->id}");

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', ['id' => $product->id]); // soft-delete
    }
}
