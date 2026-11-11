<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\ShippingMethod;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderHistoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_only_sees_own_order_history_and_cannot_open_another_buyers_order(): void
    {
        $buyer = User::factory()->create();
        $otherBuyer = User::factory()->create();
        $ownOrder = Order::factory()->create(['buyer_id' => $buyer->id, 'order_number' => 'ST-MILIK-SAYA']);
        $otherOrder = Order::factory()->create(['buyer_id' => $otherBuyer->id, 'order_number' => 'ST-MILIK-ORANG-LAIN']);

        $this->actingAs($buyer)->get(route('orders.index'))
            ->assertOk()
            ->assertSee('ST-MILIK-SAYA')
            ->assertDontSee('ST-MILIK-ORANG-LAIN');

        $this->actingAs($buyer)->get(route('orders.show', $otherOrder))->assertForbidden();
        $this->actingAs($buyer)->get(route('orders.show', $ownOrder))->assertOk();
    }

    public function test_buyer_can_filter_purchase_history_by_order_status(): void
    {
        $buyer = User::factory()->create();
        Order::factory()->create([
            'buyer_id' => $buyer->id,
            'order_number' => 'ST-PENDING',
            'status' => OrderStatus::PendingPayment,
        ]);
        Order::factory()->create([
            'buyer_id' => $buyer->id,
            'order_number' => 'ST-PAID',
            'status' => OrderStatus::Paid,
        ]);

        $this->actingAs($buyer)
            ->get(route('orders.index', ['status' => OrderStatus::Paid->value]))
            ->assertOk()
            ->assertSee('ST-PAID')
            ->assertDontSee('ST-PENDING');
    }

    public function test_seller_can_filter_sales_history_by_order_status(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $category = Category::factory()->create();
        $paidProduct = Product::factory()->for($seller, 'seller')->for($category)->create();
        $pendingProduct = Product::factory()->for($seller, 'seller')->for($category)->create();
        $paidOrder = Order::factory()->create([
            'buyer_id' => $buyer->id,
            'order_number' => 'ST-SELLER-PAID',
            'status' => OrderStatus::Paid,
            'shipping_method' => ShippingMethod::Expedition,
        ]);
        $paidOrder->orderItems()->create(['product_id' => $paidProduct->id, 'price_snapshot' => $paidProduct->price, 'qty' => 1]);
        $pendingOrder = Order::factory()->create([
            'buyer_id' => $buyer->id,
            'order_number' => 'ST-SELLER-PENDING',
            'status' => OrderStatus::PendingPayment,
            'shipping_method' => ShippingMethod::Expedition,
        ]);
        $pendingOrder->orderItems()->create(['product_id' => $pendingProduct->id, 'price_snapshot' => $pendingProduct->price, 'qty' => 1]);

        $this->actingAs($seller)
            ->get(route('seller.orders.index', ['status' => OrderStatus::Paid->value]))
            ->assertOk()
            ->assertSee('ST-SELLER-PAID')
            ->assertDontSee('ST-SELLER-PENDING');
    }
}
