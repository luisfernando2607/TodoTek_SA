<?php
namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::inRandomOrder()->first()?->id ?? 1,
            'name'        => fake()->unique()->words(3, true),
            'sku'         => strtoupper(fake()->unique()->bothify('???-####')),
            'description' => fake()->optional()->sentence(),
            'price'       => fake()->randomFloat(2, 1, 2000),
            'tax_rate'    => fake()->randomElement([0, 5, 12, 15]),
            'stock'       => fake()->numberBetween(0, 500),
            'stock_min'   => fake()->numberBetween(1, 20),
            'active'      => true,
        ];
    }
}
