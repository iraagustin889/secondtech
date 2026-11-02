<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cod_checkout_creates_immutable_snapshots_and_reserves_the_product(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $product = Product::factory()
            ->for($seller, 'seller')
            ->for(Category::factory())
            ->create(['price' => 1_500_000, 'weight' => 500, 'stock' => 1, 'status' => 'active']);

        $this->actingAs($buyer)
            ->get(route('checkout.create', $product))
            ->assertOk()
            ->assertSee($product->name);

        $response = $this->actingAs($buyer)->post(route('checkout.store', $product), [
            'recipient_name' => 'Pembeli',
            'recipient_phone' => '08123456789',
            'recipient_address' => 'Jl. Contoh 1',
            'recipient_location' => 'Bandung',
            'shipping_method' => 'cod',
        ]);

        $order = Order::query()->firstOrFail();

        $response->assertRedirectToRoute('orders.show', $order);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'buyer_id' => $buyer->id,
            'subtotal' => 1_500_000,
            'shipping_cost' => 0,
            'total' => 1_500_000,
            'status' => OrderStatus::CodRequested->value,
        ]);
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'method' => 'cod', 'status' => PaymentStatus::Pending->value]);
        $this->assertDatabaseHas('shippings', ['order_id' => $order->id, 'status' => ShippingStatus::CodRequested->value]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 0, 'status' => 'reserved']);
    }

    public function test_reserved_product_cannot_be_checked_out_a_second_time(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->for(Category::factory())->create(['stock' => 0, 'status' => 'reserved']);

        $this->actingAs($buyer)
            ->post(route('checkout.store', $product), [
                'recipient_name' => 'Pembeli', 'recipient_phone' => '08123456789', 'recipient_address' => 'Jl. Contoh 1', 'recipient_location' => 'Bandung', 'shipping_method' => 'cod',
            ])
            ->assertRedirectToRoute('catalog.index')
            ->assertSessionHas('error');

        $this->assertDatabaseCount('orders', 0);
    }
}
