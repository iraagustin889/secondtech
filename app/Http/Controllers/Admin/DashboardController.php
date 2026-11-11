<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $successfulStatuses = array_map(
            fn (OrderStatus $status): string => $status->value,
            [
                OrderStatus::Paid,
                OrderStatus::Processing,
                OrderStatus::Shipped,
                OrderStatus::Received,
                OrderStatus::Completed,
                OrderStatus::CodCompleted,
            ],
        );
        $activeStatuses = array_map(
            fn (OrderStatus $status): string => $status->value,
            [
                OrderStatus::CodRequested,
                OrderStatus::CodAgreed,
                OrderStatus::Paid,
                OrderStatus::Processing,
                OrderStatus::Shipped,
                OrderStatus::Received,
            ],
        );

        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'activeUsers' => User::where('is_active', true)->count(),
            'inactiveUsers' => User::where('is_active', false)->count(),
            'totalOrders' => Order::count(),
            'pendingPayments' => Payment::query()->where('status', PaymentStatus::Submitted->value)->count(),
            'activeOrders' => Order::query()->whereIn('status', $activeStatuses)->count(),
            'totalSales' => Order::query()->whereIn('status', $successfulStatuses)->sum('total'),
        ]);
    }
}
