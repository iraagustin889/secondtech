<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_shows_operational_transaction_metrics(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $buyer = User::factory()->create();
        $pendingOrder = Order::factory()->create([
            'buyer_id' => $buyer->id,
            'order_number' => 'ST-PENDING',
            'status' => OrderStatus::PendingPayment,
            'total' => 100_000,
        ]);
        Payment::create([
            'order_id' => $pendingOrder->id,
            'method' => 'transfer_bank',
            'status' => PaymentStatus::Submitted,
        ]);
        Order::factory()->create([
            'buyer_id' => $buyer->id,
            'order_number' => 'ST-SALE',
            'status' => OrderStatus::Completed,
            'total' => 250_000,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Total pesanan')
            ->assertSee('Menunggu verifikasi pembayaran')
            ->assertSee('Pesanan aktif')
            ->assertSee('Nilai transaksi berhasil')
            ->assertSee('Rp 250.000');
    }
}
