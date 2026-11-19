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

class AdminPaymentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_submitted_payment_queue(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [, , $payment] = $this->submittedPayment();

        $this->actingAs($admin)
            ->get(route('admin.payments.index'))
            ->assertOk()
            ->assertSee($payment->order->order_number)
            ->assertSee($payment->reference);
    }

    public function test_admin_can_reject_a_submitted_payment_and_release_the_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$order, $product, $payment] = $this->submittedPayment();

        $this->actingAs($admin)
            ->post(route('admin.orders.payment.reject', $payment->order), [
                'failure_reason' => 'Nominal transfer tidak sesuai.',
            ])
            ->assertRedirectToRoute('admin.payments.index')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => PaymentStatus::Failed->value,
            'failure_reason' => 'Nominal transfer tidak sesuai.',
            'verified_by' => $admin->id,
        ]);
        $this->assertDatabaseHas('orders', [
            'id' => $payment->order->id,
            'status' => OrderStatus::Cancelled->value,
        ]);
        $this->assertDatabaseHas('shippings', [
            'order_id' => $payment->order->id,
            'status' => ShippingStatus::Cancelled->value,
        ]);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 1,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('admin_logs', [
            'admin_id' => $admin->id,
            'action' => 'reject_payment',
            'target_id' => $payment->order->id,
        ]);

        $this->actingAs($order->buyer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('Alasan pembayaran ditolak')
            ->assertSee('Nominal transfer tidak sesuai.');
    }

    /**
     * @return array{0: Order, 1: Product, 2: Payment}
     */
    private function submittedPayment(): array
    {
        $buyer = User::factory()->create();
        $seller = User::factory()->create();
        $product = Product::factory()
            ->for($seller, 'seller')
            ->for(Category::factory())
            ->create(['status' => 'reserved', 'stock' => 0]);
        $order = Order::factory()->create([
            'buyer_id' => $buyer->id,
            'order_number' => 'ST-TEST-'.fake()->unique()->numerify('######'),
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
        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => 'transfer_bank',
            'status' => PaymentStatus::Submitted,
            'reference' => 'TRX-'.fake()->unique()->numerify('######'),
            'submitted_at' => now(),
        ]);
        Shipping::create([
            'order_id' => $order->id,
            'method' => ShippingMethod::Expedition,
            'status' => ShippingStatus::Selected,
            'weight' => $product->weight,
        ]);

        return [$order, $product, $payment];
    }
}
