<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ConversationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $product = Product::query()->purchasable()->first();

        if ($product === null) {
            return;
        }

        $buyer = User::query()
            ->whereKeyNot($product->user_id)
            ->where('role', '!=', 'admin')
            ->first();

        if ($buyer === null) {
            return;
        }

        Conversation::firstOrCreate([
            'buyer_id' => $buyer->id,
            'seller_id' => $product->user_id,
            'product_id' => $product->id,
        ]);
    }
}
