<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'buyer_id' => User::factory(),
            'subtotal' => 0,
            'service_fee' => 0,
            'total' => 0,
            'status' => OrderStatus::PendingPayment,
        ];
    }
}