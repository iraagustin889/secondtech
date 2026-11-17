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
use App\Services\MidtransGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MidtransPaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.midtrans.server_key', 'SB-Mid-server-test');
        config()->set('services.midtrans.is_production', false);
    }

    public function test_buyer_is_redirected_to_midtrans_snap_with_the_selected_payment_method(): void
    {
        [$buyer, $order, $payment] = $this->makePendingOrder('qris');
        Http::fake([
            'https://app.sandbox.midtrans.com/snap/v1/transactions' => Http::response([
                'token' => 'snap-token-123',
                'redirect_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123',
            ]),
        ]);

        $this->actingAs($buyer)
            ->post(route('orders.payment.gateway', $order))
            ->assertRedirect('https://app.sandbox.midtrans.com/snap/v2/vtweb/snap-token-123');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'gateway' => 'midtrans',
            'gateway_token' => 'snap-token-123',
        ]);

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://app.sandbox.midtrans.com/snap/v1/transactions'
            && $request['transaction_details']['gross_amount'] === (int) $order->total
            && $request['enabled_payments'] === ['qris']);
    }

    public function test_valid_midtrans_notification_releases_the_order_to_escrow_processing(): void
    {
        [$buyer, $order, $payment] = $this->makePendingOrder('bank_transfer');
        $payment->update([
            'gateway' => 'midtrans',
            'gateway_order_id' => 'ST-ORDER-123',
        ]);

        $payload = [
            'transaction_status' => 'settlement',
            'transaction_id' => 'midtrans-transaction-123',
            'payment_type' => 'qris',
            'status_code' => '200',
            'gross_amount' => number_format((float) $order->total, 2, '.', ''),
            'order_id' => 'ST-ORDER-123',
        ];
        $payload['signature_key'] = hash('sha512', implode('', [
            $payload['order_id'],
            $payload['status_code'],
            $payload['gross_amount'],
            'SB-Mid-server-test',
        ]));

        $this->postJson(route('payments.midtrans.notification'), $payload)
            ->assertOk();

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::Paid->value,
            'reference' => 'midtrans-transaction-123',
            'gateway_transaction_id' => 'midtrans-transaction-123',
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => OrderStatus::Paid->value,
        ]);
    }

    public function test_invalid_midtrans_notification_is_rejected(): void
    {
        [, $order, $payment] = $this->makePendingOrder('qris');
        $payment->update(['gateway' => 'midtrans', 'gateway_order_id' => 'ST-ORDER-INVALID']);

        $this->postJson(route('payments.midtrans.notification'), [
            'transaction_status' => 'settlement',
            'transaction_id' => 'midtrans-transaction-invalid',
            'status_code' => '200',
            'gross_amount' => number_format((float) $order->total, 2, '.', ''),
            'order_id' => 'ST-ORDER-INVALID',
            'signature_key' => 'invalid',
        ])->assertForbidden();

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => PaymentStatus::Pending->value]);
    }

    public function test_midtrans_refund_is_sent_using_the_provider_transaction_id(): void
    {
        [, $order, $payment] = $this->makePendingOrder('qris');
        $payment->update([
            'gateway' => 'midtrans',
            'gateway_order_id' => 'ST-ORDER-REFUND',
            'gateway_transaction_id' => 'midtrans-transaction-refund',
            'status' => PaymentStatus::Paid,
        ]);
        Http::fake([
            'https://api.sandbox.midtrans.com/v2/midtrans-transaction-refund/refund' => Http::response([
                'status_code' => '200',
                'transaction_status' => 'refund',
                'refund_key' => 'ST-REFUND-1',
            ]),
        ]);

        app(MidtransGateway::class)->refund($payment, (int) $order->total, 'Produk dikembalikan', 'ST-REFUND-1');

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.sandbox.midtrans.com/v2/midtrans-transaction-refund/refund'
            && $request['amount'] === (int) $order->total
            && $request['refund_key'] === 'ST-REFUND-1');
    }

    /**
     * @return array{0: User, 1: Order, 2: Payment}
     */
    private function makePendingOrder(string $method): array
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $product = Product::factory()->for($seller, 'seller')->for(Category::factory())->create([
            'price' => 1_000_000,
            'status' => 'reserved',
            'stock' => 0,
        ]);
        $order = Order::factory()->create([
            'buyer_id' => $buyer->id,
            'subtotal' => 1_000_000,
            'service_fee' => 2_500,
            'shipping_cost' => 23_000,
            'total' => 1_025_500,
            'status' => OrderStatus::PendingPayment,
            'shipping_method' => ShippingMethod::Expedition,
            'recipient_name' => $buyer->name,
            'recipient_phone' => '08123456789',
            'recipient_address' => 'Jl. Contoh 1',
            'recipient_location' => 'Bandung',
        ]);
        $order->orderItems()->create(['product_id' => $product->id, 'price_snapshot' => $product->price, 'qty' => 1]);
        $payment = Payment::create(['order_id' => $order->id, 'method' => $method, 'status' => PaymentStatus::Pending]);
        Shipping::create(['order_id' => $order->id, 'method' => ShippingMethod::Expedition, 'status' => ShippingStatus::Selected, 'weight' => $product->weight]);

        return [$buyer, $order, $payment];
    }
}
