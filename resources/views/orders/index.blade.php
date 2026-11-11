@extends('layouts.app')

@section('title', 'Riwayat Pembelian')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div><h1 class="text-2xl font-bold">Riwayat Pembelian</h1><p class="mt-1 text-gray-600">Lihat status pembayaran, pengiriman, dan pesanan Anda.</p></div>
        <a href="{{ route('catalog.index') }}" class="rounded border px-4 py-2">Belanja lagi</a>
    </div>

    <form method="GET" action="{{ route('orders.index') }}" class="mb-6 flex flex-wrap items-end gap-2">
        <div>
            <label for="status" class="block text-xs text-gray-500">Status pesanan</label>
            <select id="status" name="status" class="rounded border px-3 py-2 text-sm">
                <option value="">Semua status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($selectedStatus === $status)>{{ str_replace('_', ' ', $status->value) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Filter</button>
        @if ($selectedStatus)
            <a href="{{ route('orders.index') }}" class="rounded border px-4 py-2 text-sm hover:bg-gray-50">Reset</a>
        @endif
    </form>

    @if ($orders->isEmpty())
        <div class="rounded-xl border border-dashed bg-white p-10 text-center text-gray-600">Belum ada pesanan dengan status tersebut.</div>
    @else
        <div class="space-y-4">
            @foreach ($orders as $order)
                <a href="{{ route('orders.show', $order) }}" class="block rounded-xl border bg-white p-5 transition hover:border-blue-400 hover:shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="font-bold">{{ $order->order_number ?? '#'.$order->id }}</p>
                            <p class="mt-1 text-sm text-gray-600">{{ $order->created_at->format('d M Y H:i') }} · {{ $order->orderItems->first()?->product?->name ?? 'Produk tidak tersedia' }}</p>
                        </div>
                        <div class="text-right"><p class="font-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</p><p class="mt-1 text-sm text-blue-700">{{ str_replace('_', ' ', $order->status->value) }}</p></div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $orders->links() }}</div>
    @endif
@endsection
