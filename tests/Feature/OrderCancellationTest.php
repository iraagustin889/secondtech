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

class OrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_cancel_a_paid_order_before_it_is_processed_and_receives_a_local_refund(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $product = Product::factory()->for($seller, 'seller')->for(Category::factory())->create(['status' => 'reserved', 'stock' => 0]);
        $order = Order::factory()->create([
            'buyer_id' => $buyer->id,
            'status' => OrderStatus::Paid,
            'shipping_method' => ShippingMethod::Expedition,
        ]);
        $order->orderItems()->create(['product_id' => $product->id, 'price_snapshot' => $product->price, 'qty' => 1]);
        Payment::create(['order_id' => $order->id, 'method' => 'qris', 'status' => PaymentStatus::Paid, 'paid_at' => now()]);
        Shipping::create(['order_id' => $order->id, 'method' => ShippingMethod::Expedition, 'status' => ShippingStatus::Selected, 'weight' => $product->weight]);

        $this->actingAs($buyer)->post(route('orders.cancel', $order), [
            'reason' => 'Berubah pikiran',
            'note' => 'Ingin membeli produk lain.',
        ])->assertRedirectToRoute('orders.show', $order);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatus::Cancelled->value, 'cancellation_reason' => 'Berubah pikiran']);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => PaymentStatus::Refunded->value]);
        $this->assertDatabaseHas('shippings', ['order_id' => $order->id, 'status' => ShippingStatus::Cancelled->value]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1, 'status' => 'active']);
    }
}
