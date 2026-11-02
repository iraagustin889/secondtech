<?php

namespace Database\Factories;

use App\Enums\ShippingMethod;
use App\Enums\ShippingStatus;
use App\Models\Order;
use App\Models\Shipping;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shipping>
 */
class ShippingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'method' => ShippingMethod::Expedition,
            'status' => ShippingStatus::Waiting,
            'weight' => 1000,
        ];
    }
}
