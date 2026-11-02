@extends('layouts.app')

@section('title', 'Pesanan Masuk')

@section('content')
    <div class="mb-6"><h1 class="text-2xl font-bold">Pesanan Masuk</h1><p class="mt-1 text-gray-600">Kelola pesanan atas produk Anda.</p></div>

    @if ($orders->isEmpty())
        <div class="rounded-xl border border-dashed bg-white p-10 text-center text-gray-600">Belum ada pesanan masuk.</div>
    @else
        <div class="space-y-4">
            @foreach ($orders as $order)
                <a href="{{ route('orders.show', $order) }}" class="block rounded-xl border bg-white p-5 transition hover:border-blue-400 hover:shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div><p class="font-bold">{{ $order->order_number ?? '#'.$order->id }}</p><p class="mt-1 text-sm text-gray-600">Pembeli: {{ $order->buyer->name }} · {{ $order->orderItems->first()?->product?->name }}</p></div>
                        <div class="text-right"><p class="font-bold">{{ str_replace('_', ' ', $order->status->value) }}</p><p class="mt-1 text-sm text-gray-600">{{ $order->shipping_method?->value === 'cod' ? 'COD' : 'Ekspedisi' }}</p></div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-8">{{ $orders->links() }}</div>
    @endif
@endsection
