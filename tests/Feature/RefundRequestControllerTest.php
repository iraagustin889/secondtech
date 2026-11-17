<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
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

class RefundRequestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_open_a_return_request_and_admin_can_approve_the_refund(): void
    {
        [$buyer, $admin, $product, $order] = $this->makeShippedOrder();

        $this->actingAs($buyer)->post(route('orders.refund-requests.store', $order), [
            'reason' => 'Barang tidak sesuai',
            'description' => 'Spesifikasi barang berbeda dengan deskripsi yang tercantum.',
        ])->assertRedirectToRoute('orders.show', $order);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatus::Disputed->value]);
        $this->assertDatabaseHas('refund_requests', ['order_id' => $order->id, 'status' => RefundStatus::Requested->value]);

        $refundRequest = $order->refundRequest()->firstOrFail();

        $this->actingAs($admin)->post(route('admin.refund-requests.approve', $refundRequest), [
            'resolution_note' => 'Barang telah dikonfirmasi kembali ke penjual.',
        ])->assertRedirectToRoute('admin.refund-requests.index');

        $this->assertDatabaseHas('refund_requests', ['id' => $refundRequest->id, 'status' => RefundStatus::Approved->value, 'resolved_by' => $admin->id]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatus::Refunded->value]);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => PaymentStatus::Refunded->value]);
        $this->assertDatabaseHas('shippings', ['order_id' => $order->id, 'status' => ShippingStatus::Returned->value]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 1, 'status' => 'active']);
    }

    /** @return array{0: User, 1: User, 2: Product, 3: Order} */
    private function makeShippedOrder(): array
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->for($seller, 'seller')->for(Category::factory())->create(['status' => 'reserved', 'stock' => 0]);
        $order = Order::factory()->create(['buyer_id' => $buyer->id, 'status' => OrderStatus::Shipped, 'shipping_method' => ShippingMethod::Expedition]);
        $order->orderItems()->create(['product_id' => $product->id, 'price_snapshot' => $product->price, 'qty' => 1]);
        Payment::create(['order_id' => $order->id, 'method' => 'qris', 'status' => PaymentStatus::Paid, 'paid_at' => now()]);
        Shipping::create(['order_id' => $order->id, 'method' => ShippingMethod::Expedition, 'status' => ShippingStatus::Shipped, 'weight' => $product->weight]);

        return [$buyer, $admin, $product, $order];
    }
}
