<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Enums\ShippingStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentSimulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_automatically_verify_a_local_demo_payment(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $product = Product::factory()
            ->for($seller, 'seller')
            ->for(Category::factory())
            ->create(['status' => 'reserved', 'stock' => 0]);
        $order = Order::factory()->create([
            'buyer_id' => $buyer->id,
            'order_number' => 'ST-TEST-000002',
            'subtotal' => $product->price,
            'total' => $product->price,
            'status' => OrderStatus::PendingPayment,
            'shipping_method' => ShippingMethod::Expedition,
        ]);
        $order->orderItems()->create([
            'product_id' => $product->id,
            'price_snapshot' => $product->price,
            'qty' => 1,
        ]);
        Payment::create([
            'order_id' => $order->id,
            'method' => 'transfer_bank',
            'status' => PaymentStatus::Pending,
        ]);
        Shipping::create([
            'order_id' => $order->id,
            'method' => ShippingMethod::Expedition,
            'status' => ShippingStatus::Selected,
            'weight' => $product->weight,
        ]);

        $this->actingAs($buyer)
            ->post(route('orders.payment.simulate', $order))
            ->assertRedirectToRoute('orders.show', $order)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => PaymentStatus::Paid->value,
            'reference' => 'SIM-ST-TEST-000002',
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::Paid->value,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'status' => 'sold',
        ]);

        $payment = Payment::query()->where('order_id', $order->id)->firstOrFail();

        $this->assertNull($payment->verified_by);
        $this->assertNotNull($payment->submitted_at);
        $this->assertNotNull($payment->paid_at);
    }
}
