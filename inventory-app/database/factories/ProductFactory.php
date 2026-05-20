<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'external_id' => fake()->unique()->numberBetween(1, 999999),
            'title' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'price' => fake()->randomFloat(2, 5, 5000),
            'stock_quantity' => fake()->numberBetween(0, 500),
            'category' => fake()->optional()->word(),
            'image_url' => fake()->optional()->imageUrl(),
            'rating_rate' => fake()->optional()->randomFloat(1, 0, 5),
            'rating_count' => fake()->optional()->numberBetween(0, 100),
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock_quantity' => 0,
        ]);
    }
}
