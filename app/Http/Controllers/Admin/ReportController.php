<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    // Status yang TIDAK dihitung sebagai penjualan
    private const EXCLUDED = ['pending_payment', 'cancelled'];

    public function index(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = $request->query('from');
        $to = $request->query('to');

        $base = Order::query()
            ->when($from, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($to, fn ($q, $d) => $q->whereDate('created_at', '<=', $d));

        $sales = (clone $base)->whereNotIn('status', self::EXCLUDED);

        $summary = [
            'total_orders' => (clone $base)->count(),
            'paid_orders' => (clone $sales)->count(),
            'total_sales' => (clone $sales)->sum('subtotal'),
            'total_fee' => (clone $sales)->sum('service_fee'),
        ];

        $orders = (clone $base)
            ->with('buyer')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.reports.index', compact('summary', 'orders', 'from', 'to'));
    }
}