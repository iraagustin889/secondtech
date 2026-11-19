@extends('layouts.app')

@section('title', 'Notifikasi Penjual - SECONDTECH')

@section('content')
    <div class="mb-6"><h1 class="text-2xl font-bold">Notifikasi penjual</h1><p class="mt-1 text-gray-600">Pesanan baru akan muncul otomatis di sini dan pada menu Penjualan Saya.</p></div>

    @if ($notifications->isEmpty())
        <div class="rounded-xl border border-dashed bg-white p-10 text-center text-gray-600">Belum ada notifikasi pesanan.</div>
    @else
        <div class="space-y-3">
            @foreach ($notifications as $notification)
                @php($data = $notification->data)
                <a href="{{ route('orders.show', $data['order_id']) }}" class="block rounded-xl border bg-white p-4 transition hover:border-blue-400 hover:shadow-sm">
                    <div class="flex items-start justify-between gap-3"><div><p class="font-semibold">Pesanan baru: {{ $data['order_number'] ?? '#'.$data['order_id'] }}</p><p class="mt-1 text-sm text-gray-700">{{ $data['buyer_name'] ?? 'Pembeli' }} memesan {{ $data['product_name'] ?? 'produk Anda' }}.</p><p class="mt-1 text-sm text-gray-500">{{ $data['message'] ?? '' }}</p></div><p class="shrink-0 text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</p></div>
                </a>
            @endforeach
        </div>
        <div class="mt-6">{{ $notifications->links() }}</div>
    @endif
@endsection
