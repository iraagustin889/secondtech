<?php

namespace App\Http\Controllers\Seller;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AgreeCodRequest;
use App\Http\Requests\ConfirmCodHandoverRequest;
use App\Http\Requests\StoreShipmentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipping;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->whereHas('orderItems.product', fn ($query) => $query->where('user_id', $request->user()->id))
            ->with(['buyer', 'orderItems.product', 'payment', 'shipping'])
            ->latest()
            ->paginate(12);

        return view('seller.orders.index', compact('orders'));
    }

    public function process(Order $order): RedirectResponse
    {
        Gate::authorize('processFulfillment', $order);

        $order->update(['status' => OrderStatus::Processing]);
        $order->shipping()->update(['status' => ShippingStatus::ReadyToShip]);

        return to_route('orders.show', $order)->with('success', 'Pesanan diproses. Serahkan paket ke ekspedisi lalu masukkan nomor resi.');
    }

    public function ship(StoreShipmentRequest $request, Order $order): RedirectResponse
    {
        Gate::authorize('ship', $order);

        $order->shipping()->update([
            'resi' => $request->validated('resi'),
            'status' => ShippingStatus::Shipped,
            'tracking_status' => 'Nomor resi telah dibuat.',
        ]);
        $order->update(['status' => OrderStatus::Shipped]);

        return to_route('orders.show', $order)->with('success', 'Nomor resi disimpan dan pesanan ditandai dikirim.');
    }

    public function agreeCod(AgreeCodRequest $request, Order $order): RedirectResponse
    {
        Gate::authorize('agreeCod', $order);

        $order->shipping()->update([
            ...$request->validated(),
            'status' => ShippingStatus::CodAgreed,
        ]);
        $order->update(['status' => OrderStatus::CodAgreed]);

        return to_route('orders.show', $order)->with('success', 'Kesepakatan COD dicatat. Pembeli dan penjual dapat mengonfirmasi setelah serah-terima.');
    }

    public function confirmCod(ConfirmCodHandoverRequest $request, Order $order): RedirectResponse
    {
        Gate::authorize('confirmCod', $order);

        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            $shipping = Shipping::query()->where('order_id', $lockedOrder->id)->lockForUpdate()->firstOrFail();

            if ($lockedOrder->status !== OrderStatus::CodAgreed) {
                abort(422, 'Kesepakatan COD belum aktif atau sudah selesai.');
            }

            $shipping->update(['seller_cod_confirmed_at' => now()]);
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

        return to_route('orders.show', $order)->with('success', 'Konfirmasi COD telah dicatat.');
    }
}
