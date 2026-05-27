<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Client;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    private string $token;
    private Client $client;
    private Product $product1;
    private Product $product2;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->token = $user->createToken('test')->plainTextToken;

        $category = Category::factory()->create();
        $this->client = Client::factory()->create();

        $this->product1 = Product::factory()->create([
            'category_id' => $category->id,
            'stock'       => 50,
            'price'       => 100,
            'tax_rate'    => 15,
        ]);

        $this->product2 = Product::factory()->create([
            'category_id' => $category->id,
            'stock'       => 30,
            'price'       => 50,
            'tax_rate'    => 12,
        ]);
    }

    public function test_list_invoices(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/invoices');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'total', 'current_page']);
    }

    public function test_create_invoice_and_deduct_stock(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/invoices', [
            'client_id' => $this->client->id,
            'items'     => [
                ['product_id' => $this->product1->id, 'quantity' => 2],
                ['product_id' => $this->product2->id, 'quantity' => 3],
            ],
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'invoice_number', 'total', 'items']);

        // Verify stock was deducted
        $this->assertDatabaseHas('products', [
            'id'    => $this->product1->id,
            'stock' => 48,
        ]);
        $this->assertDatabaseHas('products', [
            'id'    => $this->product2->id,
            'stock' => 27,
        ]);
    }

    public function test_create_invoice_fails_with_insufficient_stock(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/invoices', [
            'client_id' => $this->client->id,
            'items'     => [
                ['product_id' => $this->product1->id, 'quantity' => 999],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_show_invoice(): void
    {
        $invoice = $this->createTestInvoice();

        $response = $this->withToken($this->token)->getJson("/api/invoices/{$invoice['id']}");

        $response->assertStatus(200)
                 ->assertJson(['id' => $invoice['id']]);
    }

    public function test_cancel_invoice_and_revert_stock(): void
    {
        $invoice = $this->createTestInvoice();

        $response = $this->withToken($this->token)->patchJson("/api/invoices/{$invoice['id']}/cancel");

        $response->assertStatus(200);

        // Verify stock was reverted
        $this->assertDatabaseHas('products', [
            'id'    => $this->product1->id,
            'stock' => 50,
        ]);
        $this->assertDatabaseHas('products', [
            'id'    => $this->product2->id,
            'stock' => 30,
        ]);
    }

    private function createTestInvoice(): array
    {
        $response = $this->withToken($this->token)->postJson('/api/invoices', [
            'client_id' => $this->client->id,
            'items'     => [
                ['product_id' => $this->product1->id, 'quantity' => 2],
            ],
        ]);

        return $response->json();
    }
}
