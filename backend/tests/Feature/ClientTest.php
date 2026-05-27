<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->token = $user->createToken('test')->plainTextToken;
    }

    public function test_list_clients(): void
    {
        Client::factory()->count(3)->create();

        $response = $this->withToken($this->token)->getJson('/api/clients');

        $response->assertStatus(200)
                 ->assertJsonStructure(['data', 'total', 'current_page']);
    }

    public function test_create_client(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/clients', [
            'name'           => 'Juan Pérez',
            'identification' => '0912345678',
            'email'          => 'juan@test.com',
            'phone'          => '0991234567',
        ]);

        $response->assertStatus(201)
                 ->assertJson(['name' => 'Juan Pérez']);
    }

    public function test_create_client_requires_name_and_identification(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/clients', []);

        $response->assertStatus(422);
    }

    public function test_show_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->withToken($this->token)->getJson("/api/clients/{$client->id}");

        $response->assertStatus(200)
                 ->assertJson(['id' => $client->id]);
    }

    public function test_update_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->withToken($this->token)->putJson("/api/clients/{$client->id}", [
            'name' => 'Juan Actualizado',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['name' => 'Juan Actualizado']);
    }

    public function test_delete_client(): void
    {
        $client = Client::factory()->create();

        $response = $this->withToken($this->token)->deleteJson("/api/clients/{$client->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted($client);
    }
}
