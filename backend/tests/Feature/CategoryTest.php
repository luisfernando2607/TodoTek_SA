<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->token = $user->createToken('test')->plainTextToken;
    }

    public function test_list_categories(): void
    {
        Category::factory()->count(3)->create();

        $response = $this->withToken($this->token)->getJson('/api/categories');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    public function test_create_category(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/categories', [
            'name'        => 'Electrónica',
            'slug'        => 'electronica',
            'description' => 'Equipos electrónicos',
        ]);

        $response->assertStatus(201)
                 ->assertJson(['name' => 'Electrónica', 'slug' => 'electronica']);
    }

    public function test_create_category_requires_name_and_slug(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/categories', []);

        $response->assertStatus(422);
    }

    public function test_show_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->withToken($this->token)->getJson("/api/categories/{$category->id}");

        $response->assertStatus(200)
                 ->assertJson(['id' => $category->id]);
    }

    public function test_update_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->withToken($this->token)->putJson("/api/categories/{$category->id}", [
            'name' => 'Electrónica Actualizada',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['name' => 'Electrónica Actualizada']);
    }

    public function test_delete_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->withToken($this->token)->deleteJson("/api/categories/{$category->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_unauthenticated_user_cannot_access_categories(): void
    {
        $response = $this->getJson('/api/categories');

        $response->assertStatus(401);
    }
}
