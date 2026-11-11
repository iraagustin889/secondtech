<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingStatus;
use App\Http\Requests\RejectPaymentRequest;
use App\Http\Requests\SubmitPaymentRequest;
use App\Models\AdminLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    public function index(): View
    {
        $payments = Payment::query()
            ->where('status', PaymentStatus::Submitted->value)
            ->with(['order.buyer'])
            ->latest('submitted_at')
            ->paginate(15);

        return view('admin.payments.index', compact('payments'));
    }

    public function submit(SubmitPaymentRequest $request, Order $order): RedirectResponse
    {
        Gate::authorize('submitPayment', $order);

        $payment = $order->payment;

        if ($payment?->status !== PaymentStatus::Pending) {
            return back()->with('error', 'Pembayaran ini tidak dapat dikirim ulang.');
        }

        $payment->update([
            'reference' => $request->validated('reference'),
            'status' => PaymentStatus::Submitted,
            'submitted_at' => now(),
        ]);

        return to_route('orders.show', $order)->with('success', 'Referensi pembayaran dikirim dan menunggu verifikasi admin.');
    }

    public function confirm(Order $order): RedirectResponse
    {
        Gate::authorize('verifyPayment', $order);

        $this->markAsPaid($order, PaymentStatus::Submitted, auth()->id());

        return to_route('orders.show', $order)->with('success', 'Pembayaran dikonfirmasi. Pesanan siap diproses penjual.');
    }

    public function simulate(Order $order): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        Gate::authorize('submitPayment', $order);

        $this->markAsPaid(
            $order,
            PaymentStatus::Pending,
            null,
            'SIM-'.($order->order_number ?? $order->id),
        );

        return to_route('orders.show', $order)->with('success', 'Pembayaran demo berhasil diverifikasi otomatis. Pesanan siap diproses penjual.');
    }

    public function reject(RejectPaymentRequest $request, Order $order): RedirectResponse
    {
        Gate::authorize('verifyPayment', $order);

        $reason = $request->validated('failure_reason');

        DB::transaction(function () use ($order, $reason): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $payment = Payment::query()->where('order_id', $lockedOrder->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== OrderStatus::PendingPayment || $payment->status !== PaymentStatus::Submitted) {
                abort(422, 'Pembayaran belum siap untuk ditolak atau sudah diproses.');
            }

            $payment->update([
                'status' => PaymentStatus::Failed,
                'failure_reason' => $reason,
                'verified_by' => auth()->id(),
            ]);
            $lockedOrder->update([
                'status' => OrderStatus::Cancelled,
                'cancelled_at' => now(),
            ]);
            Shipping::query()->where('order_id', $lockedOrder->id)->update(['status' => ShippingStatus::Cancelled]);

            foreach ($lockedOrder->orderItems as $item) {
                $product = Product::query()->lockForUpdate()->find($item->product_id);

                if ($product?->status === 'reserved') {
                    $product->update([
                        'stock' => $product->stock + $item->qty,
                        'status' => 'active',
                    ]);
                }
            }

            AdminLog::record('reject_payment', 'order', $lockedOrder->id, $reason);
        });

        return to_route('admin.payments.index')->with('status', 'Pembayaran ditolak dan produk dikembalikan ke katalog.');
    }

    private function markAsPaid(
        Order $order,
        PaymentStatus $expectedPaymentStatus,
        ?int $verifiedBy,
        ?string $reference = null,
    ): void {
        DB::transaction(function () use ($order, $expectedPaymentStatus, $verifiedBy, $reference): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $payment = Payment::query()->where('order_id', $lockedOrder->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== OrderStatus::PendingPayment || $payment->status !== $expectedPaymentStatus) {
                abort(422, 'Pembayaran belum siap untuk diproses atau sudah diproses.');
            }

            $paymentData = [
                'status' => PaymentStatus::Paid,
                'paid_at' => now(),
                'verified_by' => $verifiedBy,
            ];

            if ($reference !== null) {
                $paymentData['reference'] = $reference;
                $paymentData['submitted_at'] = now();
            }

            $payment->update($paymentData);
            $lockedOrder->update(['status' => OrderStatus::Paid]);

            $productIds = $lockedOrder->orderItems()->pluck('product_id');
            Product::query()->whereIn('id', $productIds)->where('status', 'reserved')->update(['status' => 'sold']);
        });
    }
}
