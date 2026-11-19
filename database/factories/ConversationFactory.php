<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $seller = User::factory();

        return [
            'buyer_id' => User::factory(),
            'seller_id' => $seller,
            'product_id' => Product::factory()->for($seller, 'seller'),
        ];
    }
}
