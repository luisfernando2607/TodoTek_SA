<?php
namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'name'           => fake()->name(),
            'identification' => fake()->unique()->numerify('##########'),
            'email'          => fake()->unique()->safeEmail(),
            'phone'          => fake()->phoneNumber(),
            'address'        => fake()->address(),
            'active'         => true,
        ];
    }
}
