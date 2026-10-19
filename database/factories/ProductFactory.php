<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::inRandomOrder()->value('id'),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price' => fake()->numberBetween(50, 5000) * 1000,
            'condition' => fake()->randomElement(['like new', 'bekas baik', 'bekas layak']),
            'weight' => fake()->numberBetween(100, 5000),
            'location' => fake()->city(),
            'stock' => 1,
            'status' => 'active',
        ];
    }
}