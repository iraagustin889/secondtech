<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderReceiptController extends Controller
{
    public function __invoke(Request $request, Order $order): RedirectResponse
    {
        // Hanya pembeli pemilik pesanan
        abort_unless($order->buyer_id === $request->user()->id, 403);

        DB::transaction(function () use ($order): void {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);

            if (! in_array($locked->status, [OrderStatus::Shipped, OrderStatus::Received], true)) {
                abort(422, 'Pesanan belum dapat dikonfirmasi diterima.');
            }

            $locked->update(['status' => OrderStatus::Completed]);

            $productIds = $locked->orderItems()->pluck('product_id');
            Product::query()->whereIn('id', $productIds)->where('status', 'reserved')->update(['status' => 'sold']);
        });

        return to_route('orders.show', $order)->with('success', 'Terima kasih, pesanan dikonfirmasi selesai.');
    }
}