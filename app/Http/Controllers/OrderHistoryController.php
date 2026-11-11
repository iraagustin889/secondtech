<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Enums\ShippingStatus;
use App\Exceptions\ShippingQuoteUnavailableException;
use App\Http\Requests\ConfirmCodHandoverRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use App\Services\ShippingQuoteService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class OrderHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate(['status' => ['nullable', 'string', 'max:50']]);
        $selectedStatus = OrderStatus::tryFrom((string) ($filters['status'] ?? ''));

        $orders = $request->user()->purchases()
            ->with(['orderItems.product', 'payment', 'shipping'])
            ->when($selectedStatus, fn ($query, OrderStatus $status) => $query->where('status', $status))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $statuses = OrderStatus::cases();

        return view('orders.index', compact('orders', 'selectedStatus', 'statuses'));
    }

    public function show(Order $order): View
    {
        Gate::authorize('view', $order);

        return view('orders.show', [
            'order' => $order->load(['buyer', 'orderItems.product.seller', 'payment.verifier', 'shipping']),
        ]);
    }

    public function cancel(Order $order): RedirectResponse
    {
        Gate::authorize('cancel', $order);

        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);

            if (! in_array($lockedOrder->status, [OrderStatus::PendingPayment, OrderStatus::CodRequested], true)) {
                abort(422, 'Pesanan ini tidak dapat dibatalkan.');
            }

            $lockedOrder->update([
                'status' => OrderStatus::Cancelled,
                'cancelled_at' => now(),
            ]);

            Payment::query()->where('order_id', $lockedOrder->id)->update([
                'status' => PaymentStatus::Failed,
                'failure_reason' => 'Dibatalkan oleh pembeli.',
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
        });

        return to_route('orders.show', $order)->with('success', 'Pesanan dibatalkan dan produk dikembalikan ke katalog.');
    }

    public function confirmCod(ConfirmCodHandoverRequest $request, Order $order): RedirectResponse
    {
        Gate::authorize('confirmCod', $order);

        $this->recordCodConfirmation($request->user()->id, $order);

        return to_route('orders.show', $order)->with('success', 'Konfirmasi COD telah dicatat.');
    }

    public function tracking(Order $order, ShippingQuoteService $shippingQuotes): View|RedirectResponse
    {
        Gate::authorize('view', $order);

        $shipping = $order->shipping;
        abort_unless($shipping?->method === ShippingMethod::Expedition, 404);

        try {
            $tracking = $shippingQuotes->tracking($shipping, $order->recipient_phone);
        } catch (ShippingQuoteUnavailableException $exception) {
            return to_route('orders.show', $order)->with('error', $exception->getMessage());
        }

        $shipping->update([
            'tracking_status' => $tracking['status'],
            'tracking_checked_at' => now(),
            'status' => $tracking['delivered'] ? ShippingStatus::Delivered : $shipping->status,
        ]);

        if ($tracking['delivered'] && $order->status === OrderStatus::Shipped) {
            $order->update(['status' => OrderStatus::Received]);
        }

        return view('orders.tracking', compact('order', 'shipping', 'tracking'));
    }

    private function recordCodConfirmation(int $userId, Order $order): void
    {
        DB::transaction(function () use ($userId, $order): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $shipping = Shipping::query()->where('order_id', $lockedOrder->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== OrderStatus::CodAgreed) {
                abort(422, 'Kesepakatan COD belum aktif atau sudah selesai.');
            }

            $confirmation = $lockedOrder->buyer_id === $userId
                ? ['buyer_cod_confirmed_at' => now()]
                : ['seller_cod_confirmed_at' => now()];
            $shipping->update($confirmation);
            $shipping->refresh();

            if ($shipping->buyer_cod_confirmed_at && $shipping->seller_cod_confirmed_at) {
                $shipping->update(['status' => ShippingStatus::CodCompleted]);
                Payment::query()->where('order_id', $lockedOrder->id)->update([
                    'status' => PaymentStatus::Paid,
                    'paid_at' => now(),
                ]);
                $lockedOrder->update(['status' => OrderStatus::Completed]);

                $productIds = $lockedOrder->orderItems()->pluck('product_id');
                Product::query()->whereIn('id', $productIds)->where('status', 'reserved')->update(['status' => 'sold']);
            }
        });
    }
}
