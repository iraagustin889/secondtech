<?php

namespace Database\Seeders;

use App\Models\Conversation;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $conversation = Conversation::query()->first();

        if ($conversation === null) {
            return;
        }

        $conversation->messages()->firstOrCreate([
            'sender_id' => $conversation->buyer_id,
            'body' => 'Halo, apakah produk ini masih tersedia?',
        ]);

        $conversation->messages()->firstOrCreate([
            'sender_id' => $conversation->seller_id,
            'body' => 'Halo, produknya masih tersedia. Silakan tanya jika ada yang ingin diketahui.',
        ]);
    }
}
