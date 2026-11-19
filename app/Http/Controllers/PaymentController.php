<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingStatus;
use App\Exceptions\PaymentGatewayException;
use App\Http\Requests\RejectPaymentRequest;
use App\Http\Requests\SubmitPaymentRequest;
use App\Models\AdminLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use App\Services\MidtransGateway;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\HttpException;

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

        return to_route('orders.show', $order)->with('success', 'Pembayaran berhasil. Dana ditahan aman oleh rekber lokal sampai pesanan selesai.');
    }

    public function startGateway(Order $order, MidtransGateway $midtrans): RedirectResponse
    {
        Gate::authorize('submitPayment', $order);

        $payment = $order->payment;

        if ($payment?->status !== PaymentStatus::Pending) {
            return back()->with('error', 'Pembayaran ini tidak dapat dibuat ulang.');
        }

        if (filled($payment->gateway_redirect_url)) {
            return redirect()->away($payment->gateway_redirect_url);
        }

        try {
            $transaction = $midtrans->createSnapTransaction($order);
        } catch (PaymentGatewayException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        $payment->update([
            'gateway' => 'midtrans',
            'gateway_order_id' => $transaction['order_id'],
            'gateway_token' => $transaction['token'],
            'gateway_redirect_url' => $transaction['redirect_url'],
        ]);

        return redirect()->away($transaction['redirect_url']);
    }

    public function midtransNotification(Request $request, MidtransGateway $midtrans): JsonResponse
    {
        $payload = $request->json()->all();

        if (! is_array($payload) || ! $midtrans->hasValidSignature($payload)) {
            return response()->json(['message' => 'Signature pembayaran tidak valid.'], 403);
        }

        $payment = Payment::query()
            ->with('order')
            ->where('gateway', 'midtrans')
            ->where('gateway_order_id', (string) ($payload['order_id'] ?? ''))
            ->first();

        if (! $payment?->order) {
            return response()->json(['message' => 'Pesanan pembayaran tidak ditemukan.'], 404);
        }

        if (number_format((float) $payment->order->total, 2, '.', '') !== number_format((float) ($payload['gross_amount'] ?? 0), 2, '.', '')) {
            return response()->json(['message' => 'Nominal pembayaran tidak sesuai.'], 422);
        }

        $gatewayData = [
            'gateway_transaction_id' => $payload['transaction_id'] ?? null,
            'gateway_response' => Arr::only($payload, [
                'transaction_status',
                'transaction_id',
                'payment_type',
                'status_code',
                'gross_amount',
                'settlement_time',
                'fraud_status',
            ]),
        ];

        if (! $midtrans->isSuccessful($payload)) {
            $payment->update($gatewayData);

            return response()->json(['message' => 'Status pembayaran dicatat.']);
        }

        try {
            $this->markAsPaid(
                $payment->order,
                PaymentStatus::Pending,
                null,
                (string) ($payload['transaction_id'] ?? $payment->gateway_order_id),
                $gatewayData,
            );
        } catch (HttpException) {
            // Notifikasi Midtrans dapat dikirim ulang. Status yang sudah sukses tetap dianggap berhasil.
        }

        return response()->json(['message' => 'Pembayaran berhasil diproses.']);
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
        array $extraPaymentData = [],
    ): void {
        DB::transaction(function () use ($order, $expectedPaymentStatus, $verifiedBy, $reference, $extraPaymentData): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $payment = Payment::query()->where('order_id', $lockedOrder->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status === OrderStatus::Paid && $payment->status === PaymentStatus::Paid) {
                return;
            }

            if ($lockedOrder->status !== OrderStatus::PendingPayment || $payment->status !== $expectedPaymentStatus) {
                abort(422, 'Pembayaran belum siap untuk diproses atau sudah diproses.');
            }

            $paymentData = array_merge([
                'status' => PaymentStatus::Paid,
                'paid_at' => now(),
                'verified_by' => $verifiedBy,
            ], $extraPaymentData);

            if ($reference !== null) {
                $paymentData['reference'] = $reference;
                $paymentData['submitted_at'] = now();
            }

            $payment->update($paymentData);
            $lockedOrder->update(['status' => OrderStatus::Paid]);

            // Produk tetap berstatus reserved sampai pembeli mengonfirmasi pesanan selesai.
            // Ini menjaga dana dan stok dalam alur rekber lokal.
        });
    }
}
