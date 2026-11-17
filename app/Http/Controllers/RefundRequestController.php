<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\RefundStatus;
use App\Enums\ShippingStatus;
use App\Http\Requests\ResolveRefundRequest;
use App\Http\Requests\StoreRefundRequest;
use App\Models\AdminLog;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\RefundRequest;
use App\Models\Shipping;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class RefundRequestController extends Controller
{
    public function store(StoreRefundRequest $request, Order $order): RedirectResponse
    {
        abort_unless($order->buyer_id === $request->user()->id, 403);

        $data = $request->validated();

        DB::transaction(function () use ($order, $request, $data): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if (! in_array($lockedOrder->status, [OrderStatus::Shipped, OrderStatus::Received], true)) {
                abort(422, 'Pengajuan pengembalian hanya tersedia setelah pesanan dikirim atau diterima.');
            }

            if (RefundRequest::query()->where('order_id', $lockedOrder->id)->exists()) {
                abort(422, 'Pengajuan pengembalian untuk pesanan ini sudah ada.');
            }

            RefundRequest::create([
                'order_id' => $lockedOrder->id,
                'buyer_id' => $request->user()->id,
                'reason' => $data['reason'],
                'description' => $data['description'],
                'status' => RefundStatus::Requested,
                'previous_order_status' => $lockedOrder->status->value,
            ]);

            $lockedOrder->update(['status' => OrderStatus::Disputed]);
        });

        return to_route('orders.show', $order)->with('success', 'Pengajuan pengembalian dikirim. Dana tetap ditahan sampai admin menyelesaikan pemeriksaan.');
    }

    public function index(): View
    {
        $refundRequests = RefundRequest::query()
            ->with(['order.buyer', 'order.orderItems.product', 'buyer'])
            ->latest()
            ->paginate(20);

        return view('admin.refund-requests.index', compact('refundRequests'));
    }

    public function approve(ResolveRefundRequest $request, RefundRequest $refundRequest): RedirectResponse
    {
        $this->resolve($refundRequest, RefundStatus::Approved, $request->validated('resolution_note'));

        return to_route('admin.refund-requests.index')->with('status', 'Pengembalian disetujui. Dana dan stok telah diperbarui.');
    }

    public function reject(ResolveRefundRequest $request, RefundRequest $refundRequest): RedirectResponse
    {
        $this->resolve($refundRequest, RefundStatus::Rejected, $request->validated('resolution_note'));

        return to_route('admin.refund-requests.index')->with('status', 'Pengajuan pengembalian ditolak dan pesanan dilanjutkan.');
    }

    private function resolve(RefundRequest $refundRequest, RefundStatus $resolution, ?string $note): void
    {
        DB::transaction(function () use ($refundRequest, $resolution, $note): void {
            $lockedRefund = RefundRequest::query()->lockForUpdate()->findOrFail($refundRequest->id);

            if ($lockedRefund->status !== RefundStatus::Requested) {
                abort(422, 'Pengajuan ini sudah diproses.');
            }

            $order = Order::query()->lockForUpdate()->findOrFail($lockedRefund->order_id);

            if ($order->status !== OrderStatus::Disputed) {
                abort(422, 'Status pesanan tidak sesuai untuk pengembalian.');
            }

            $lockedRefund->update([
                'status' => $resolution,
                'resolution_note' => $note,
                'resolved_by' => auth()->id(),
                'resolved_at' => now(),
            ]);

            if ($resolution === RefundStatus::Rejected) {
                $order->update([
                    'status' => OrderStatus::from($lockedRefund->previous_order_status),
                ]);
                AdminLog::record('reject_refund_request', 'refund_request', $lockedRefund->id, $note);

                return;
            }

            $payment = Payment::query()->where('order_id', $order->id)->lockForUpdate()->firstOrFail();

            if ($payment->status !== PaymentStatus::Paid) {
                abort(422, 'Pembayaran pesanan ini tidak dapat dikembalikan.');
            }

            $payment->update([
                'status' => PaymentStatus::Refunded,
                'refunded_at' => now(),
                'failure_reason' => 'Pengembalian disetujui: '.$lockedRefund->reason,
            ]);
            $order->update(['status' => OrderStatus::Refunded]);
            Shipping::query()->where('order_id', $order->id)->update(['status' => ShippingStatus::Returned]);

            foreach ($order->orderItems as $item) {
                $product = Product::query()->lockForUpdate()->find($item->product_id);

                if ($product) {
                    $product->update([
                        'stock' => $product->stock + $item->qty,
                        'status' => $product->status === 'hidden' ? 'hidden' : 'active',
                    ]);
                }
            }

            AdminLog::record('approve_refund_request', 'refund_request', $lockedRefund->id, $note);
        });
    }
}
