<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_email' => fake()->unique()->safeEmail(),
            'status' => fake()->randomElement(['aguardando', 'aprovado', 'descartado']),
            'amount' => fake()->randomFloat(2, 20, 1000),
        ];
    }

    public function awaiting(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'aguardando',
        ]);
    }
}
