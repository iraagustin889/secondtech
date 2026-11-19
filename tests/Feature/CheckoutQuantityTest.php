<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutQuantityTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_order_multiple_units_without_hiding_remaining_stock(): void
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $product = Product::factory()->for($seller, 'seller')->for(Category::factory())->create([
            'price' => 250_000,
            'weight' => 400,
            'stock' => 5,
            'status' => 'active',
        ]);

        $this->actingAs($buyer)->post(route('checkout.store', $product), [
            'recipient_name' => 'Pembeli',
            'recipient_phone' => '08123456789',
            'recipient_address' => 'Jl. Contoh 1',
            'recipient_location' => 'Bandung',
            'quantity' => 3,
            'shipping_method' => 'cod',
        ])->assertRedirect();

        $order = Order::query()->firstOrFail();
        $this->assertSame(OrderStatus::CodRequested, $order->status);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => $product->id,
            'price_snapshot' => 250_000,
            'qty' => 3,
        ]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 2, 'status' => 'active']);
        $this->assertDatabaseHas('shippings', ['order_id' => $order->id, 'weight' => 1200]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $seller->id]);

        $this->actingAs($buyer)->post(route('orders.cancel', $order))
            ->assertRedirectToRoute('orders.show', $order);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock' => 5, 'status' => 'active']);
    }

    public function test_account_cannot_check_out_its_own_product(): void
    {
        $seller = User::factory()->create();
        $product = Product::factory()->for($seller, 'seller')->for(Category::factory())->create();

        $this->actingAs($seller)
            ->get(route('checkout.create', $product))
            ->assertForbidden();

        $this->assertDatabaseCount('orders', 0);
    }
}
