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

class PaymentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_confirm_a_submitted_transfer_and_release_it_to_seller_processing(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->for($seller, 'seller')->for(Category::factory())->create(['status' => 'reserved', 'stock' => 0]);
        $order = Order::factory()->create([
            'buyer_id' => $buyer->id,
            'order_number' => 'ST-TEST-000001',
            'subtotal' => $product->price,
            'total' => $product->price,
            'status' => OrderStatus::PendingPayment,
            'shipping_method' => ShippingMethod::Expedition,
        ]);
        $order->orderItems()->create(['product_id' => $product->id, 'price_snapshot' => $product->price, 'qty' => 1]);
        Payment::create(['order_id' => $order->id, 'method' => 'transfer_bank', 'status' => PaymentStatus::Pending]);
        Shipping::create(['order_id' => $order->id, 'method' => ShippingMethod::Expedition, 'status' => ShippingStatus::Selected, 'weight' => $product->weight]);

        $this->actingAs($buyer)
            ->post(route('orders.payment.submit', $order), ['reference' => 'TRX-12345'])
            ->assertRedirectToRoute('orders.show', $order);

        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => PaymentStatus::Submitted->value, 'reference' => 'TRX-12345']);

        $this->actingAs($admin)
            ->post(route('admin.orders.payment.confirm', $order))
            ->assertRedirectToRoute('orders.show', $order);

        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => PaymentStatus::Paid->value, 'verified_by' => $admin->id]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatus::Paid->value]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'sold']);
    }

    public function test_buyer_cannot_confirm_a_payment(): void
    {
        $buyer = User::factory()->create();
        $order = Order::factory()->create(['buyer_id' => $buyer->id]);

        $this->actingAs($buyer)
            ->post(route('admin.orders.payment.confirm', $order))
            ->assertForbidden();
    }
}
