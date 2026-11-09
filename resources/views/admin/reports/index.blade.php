@extends('layouts.admin')

@section('title', 'Laporan Transaksi')
@section('heading', 'Laporan Transaksi')

@section('content')
    <form method="GET" action="{{ route('admin.reports.index') }}" class="mb-4 flex flex-wrap items-end gap-2">
        <div>
            <label class="block text-xs text-gray-500">Dari tanggal</label>
            <input type="date" name="from" value="{{ $from }}" class="rounded border px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500">Sampai tanggal</label>
            <input type="date" name="to" value="{{ $to }}" class="rounded border px-3 py-2 text-sm">
        </div>
        <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Filter</button>
        @if ($from || $to)
            <a href="{{ route('admin.reports.index') }}" class="rounded border px-4 py-2 text-sm hover:bg-gray-50">Reset</a>
        @endif
    </form>

    @error('to')
        <p class="mb-3 text-xs text-red-600">{{ $message }}</p>
    @enderror

    <div class="mb-6 grid gap-4 sm:grid-cols-4">
        <div class="rounded-lg border bg-white p-5">
            <p class="text-sm text-gray-500">Total pesanan</p>
            <p class="mt-1 text-2xl font-bold">{{ $summary['total_orders'] }}</p>
        </div>
        <div class="rounded-lg border bg-white p-5">
            <p class="text-sm text-gray-500">Pesanan terjual</p>
            <p class="mt-1 text-2xl font-bold text-green-600">{{ $summary['paid_orders'] }}</p>
        </div>
        <div class="rounded-lg border bg-white p-5">
            <p class="text-sm text-gray-500">Total penjualan</p>
            <p class="mt-1 text-2xl font-bold text-blue-600">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</p>
        </div>
        <div class="rounded-lg border bg-white p-5">
            <p class="text-sm text-gray-500">Total biaya layanan</p>
            <p class="mt-1 text-2xl font-bold">Rp {{ number_format($summary['total_fee'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-3">No. Pesanan</th>
                    <th class="px-4 py-3">Pembeli</th>
                    <th class="px-4 py-3">Subtotal</th>
                    <th class="px-4 py-3">Biaya layanan</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Tanggal</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($orders as $order)
                    <tr>
                        <td class="px-4 py-3">{{ $order->order_number ?? '#'.$order->id }}</td>
                        <td class="px-4 py-3">{{ $order->buyer->name ?? '-' }}</td>
                        <td class="px-4 py-3">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">Rp {{ number_format($order->service_fee, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ $order->status->value }}</td>
                        <td class="px-4 py-3">{{ $order->created_at->format('d M Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada transaksi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $orders->links() }}
    </div>
@endsection