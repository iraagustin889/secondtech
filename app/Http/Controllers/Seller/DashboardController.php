<?php

namespace App\Http\Controllers\Seller;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $sellerId = $request->user()->id;
        $orders = Order::query()
            ->whereHas('orderItems.product', fn ($query) => $query->where('user_id', $sellerId))
            ->with(['buyer', 'orderItems.product', 'payment', 'shipping'])
            ->latest()
            ->take(8)
            ->get();

        $salesStatuses = [
            OrderStatus::Paid,
            OrderStatus::Processing,
            OrderStatus::Shipped,
            OrderStatus::Received,
            OrderStatus::Completed,
        ];

        return view('seller.dashboard', [
            'recentOrders' => $orders,
            'activeProductCount' => $request->user()->products()->where('status', 'active')->count(),
            'pendingOrderCount' => $orders->filter(fn (Order $order): bool => in_array($order->status, [OrderStatus::Paid, OrderStatus::Processing], true))->count(),
            'shippingOrderCount' => $orders->filter(fn (Order $order): bool => $order->status === OrderStatus::Shipped)->count(),
            'salesTotal' => $orders
                ->filter(fn (Order $order): bool => in_array($order->status, $salesStatuses, true))
                ->sum('total'),
        ]);
    }
}
