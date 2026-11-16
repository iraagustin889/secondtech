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
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
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

    public function test_checkout_stores_a_quote_returned_by_rajaongkir(): void
    {
        config()->set('services.rajaongkir.key', 'configured-api-key');
        config()->set('services.rajaongkir.default_origin_id', 31_073);
        Http::fake([
            '*calculate/domestic-cost' => Http::response([
                'meta' => ['status' => 'success'],
                'data' => [[
                    'code' => 'jne',
                    'service' => 'REG',
                    'cost' => 23_000,
                    'etd' => '1 day',
                ]],
            ]),
        ]);

        $buyer = User::factory()->create();
        $product = Product::factory()->for(Category::factory())->create([
            'weight' => 500,
            'stock' => 1,
            'status' => 'active',
            'shipping_origin_id' => null,
        ]);

        $response = $this->actingAs($buyer)->post(route('checkout.quote', $product), [
            'recipient_name' => 'Pembeli',
            'recipient_phone' => '08123456789',
            'recipient_address' => 'Jl. Contoh 1',
            'recipient_location' => 'Bandung',
            'destination_id' => 4816,
            'destination_name' => 'Bandung, Jawa Barat',
        ]);

        $response->assertRedirectToRoute('checkout.create', $product)
            ->assertSessionHas('checkout.product.'.$product->id.'.quotes.0.courier', 'jne')
            ->assertSessionHas('checkout.product.'.$product->id.'.quotes.0.cost', 23_000);

        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/calculate/domestic-cost')
            && $request['origin'] === 31_073
            && $request['destination'] === 4816
            && $request['weight'] === 500);
    }

    public function test_destination_search_uses_rajaongkir_response(): void
    {
        config()->set('services.rajaongkir.key', 'configured-api-key');
        Http::fake([
            '*destination/domestic-destination*' => Http::response([
                'meta' => ['status' => 'success'],
                'data' => [[
                    'id' => 4816,
                    'label' => 'Bandung, Jawa Barat',
                ]],
            ]),
        ]);

        $buyer = User::factory()->create();

        $this->actingAs($buyer)
            ->getJson(route('shipping.destinations', ['q' => 'Bandung']))
            ->assertOk()
            ->assertJsonPath('data.0.id', 4816)
            ->assertJsonPath('data.0.name', 'Bandung, Jawa Barat');

        Http::assertSent(fn (Request $request): bool => str_starts_with($request->url(), 'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination')
            && $request['search'] === 'Bandung');
    }
}
