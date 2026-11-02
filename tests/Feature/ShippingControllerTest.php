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

class ShippingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_process_a_paid_order_and_submit_a_resi(): void
    {
        [, $seller, , $order] = $this->makeOrder(OrderStatus::Paid, ShippingMethod::Expedition, 'sold');

        $this->actingAs($seller)->post(route('seller.orders.process', $order))
            ->assertRedirectToRoute('orders.show', $order);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatus::Processing->value]);
        $this->assertDatabaseHas('shippings', ['order_id' => $order->id, 'status' => ShippingStatus::ReadyToShip->value]);

        $this->actingAs($seller)->post(route('seller.orders.ship', $order), ['resi' => 'JNE123456789'])
            ->assertRedirectToRoute('orders.show', $order);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatus::Shipped->value]);
        $this->assertDatabaseHas('shippings', ['order_id' => $order->id, 'status' => ShippingStatus::Shipped->value, 'resi' => 'JNE123456789']);
    }

    public function test_cod_is_completed_only_after_buyer_and_seller_confirm_handover(): void
    {
        [$buyer, $seller, $product, $order] = $this->makeOrder(OrderStatus::CodRequested, ShippingMethod::Cod, 'reserved');

        $this->actingAs($seller)->post(route('seller.orders.cod.agree', $order), [
            'cod_location' => 'Lobi Mall Contoh',
            'cod_meeting_at' => now()->addDay()->format('Y-m-d\\TH:i'),
        ])->assertRedirectToRoute('orders.show', $order);

        $this->actingAs($buyer)->post(route('orders.cod.confirm', $order))
            ->assertRedirectToRoute('orders.show', $order);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatus::CodAgreed->value]);

        $this->actingAs($seller)->post(route('seller.orders.cod.confirm', $order))
            ->assertRedirectToRoute('orders.show', $order);

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => OrderStatus::Completed->value]);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => PaymentStatus::Paid->value]);
        $this->assertDatabaseHas('shippings', ['order_id' => $order->id, 'status' => ShippingStatus::CodCompleted->value]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'sold']);
    }

    /**
     * @return array{0: User, 1: User, 2: Product, 3: Order}
     */
    private function makeOrder(OrderStatus $status, ShippingMethod $method, string $productStatus): array
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $product = Product::factory()->for($seller, 'seller')->for(Category::factory())->create(['status' => $productStatus, 'stock' => 0]);
        $order = Order::factory()->create([
            'buyer_id' => $buyer->id,
            'order_number' => 'ST-TEST-'.fake()->unique()->numerify('######'),
            'subtotal' => $product->price,
            'total' => $product->price,
            'status' => $status,
            'shipping_method' => $method,
            'recipient_name' => $buyer->name,
            'recipient_phone' => '08123456789',
            'recipient_address' => 'Jl. Contoh 1',
            'recipient_location' => 'Bandung',
        ]);
        $order->orderItems()->create(['product_id' => $product->id, 'price_snapshot' => $product->price, 'qty' => 1]);
        Payment::create(['order_id' => $order->id, 'method' => $method === ShippingMethod::Cod ? 'cod' : 'transfer_bank', 'status' => $status === OrderStatus::Paid ? PaymentStatus::Paid : PaymentStatus::Pending]);
        Shipping::create(['order_id' => $order->id, 'method' => $method, 'status' => $method === ShippingMethod::Cod ? ShippingStatus::CodRequested : ShippingStatus::Selected, 'weight' => $product->weight]);

        return [$buyer, $seller, $product, $order];
    }
}
