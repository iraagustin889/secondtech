<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Requests\SubmitPaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
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

        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $payment = Payment::query()->where('order_id', $lockedOrder->id)->lockForUpdate()->firstOrFail();

            if ($payment->status !== PaymentStatus::Submitted) {
                abort(422, 'Pembayaran belum dikirim pembeli atau sudah diproses.');
            }

            $payment->update([
                'status' => PaymentStatus::Paid,
                'paid_at' => now(),
                'verified_by' => auth()->id(),
            ]);
            $lockedOrder->update(['status' => OrderStatus::Paid]);

            $productIds = $lockedOrder->orderItems()->pluck('product_id');
            Product::query()->whereIn('id', $productIds)->where('status', 'reserved')->update(['status' => 'sold']);
        });

        return to_route('orders.show', $order)->with('success', 'Pembayaran dikonfirmasi. Pesanan siap diproses penjual.');
    }
}
