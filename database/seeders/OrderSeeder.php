<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::factory()->count(10)->create();
        $statuses = OrderStatus::cases();

        User::factory()->count(3)->create()->each(function (User $buyer) use ($products, $statuses) {
            $order = Order::create([
                'buyer_id' => $buyer->id,
                'subtotal' => 0,
                'service_fee' => 0,
                'total' => 0,
                'status' => $statuses[array_rand($statuses)],
            ]);

            $subtotal = 0;
            foreach ($products->random(2) as $product) {
                $item = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'price_snapshot' => $product->price,
                    'qty' => 1,
                ]);
                $subtotal += $item->price_snapshot * $item->qty;
            }

            $fee = 2500;
            $order->update([
                'subtotal' => $subtotal,
                'service_fee' => $fee,
                'total' => $subtotal + $fee,
            ]);
        });
    }
}