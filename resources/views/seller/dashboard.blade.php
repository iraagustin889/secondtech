@extends('layouts.app')

@section('title', 'Dashboard Penjual')

@section('content')
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:p-7">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#1257d6]">Pusat kendali mitra toko</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Overview Bisnis & Sirkulasi Unit</h1>
                <p class="mt-2 max-w-2xl text-sm text-slate-600">Kelola stok, pesanan, pengiriman, dan komunikasi pembeli dari satu halaman.</p>
            </div>
            <a href="{{ route('seller.products.create') }}" class="rounded-lg bg-[#1257d6] px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">+ Posting Barang Baru</a>
        </div>

        <div class="mt-7 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <section class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs text-slate-500">Produk aktif</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $activeProductCount }} <span class="text-sm font-medium text-slate-500">unit</span></p>
                <a href="{{ route('seller.products.index') }}" class="mt-3 inline-block text-xs font-semibold text-[#1257d6] hover:underline">Kelola produk →</a>
            </section>
            <section class="rounded-xl border border-rose-100 bg-rose-50/60 p-4">
                <p class="text-xs text-slate-500">Perlu diproses</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $pendingOrderCount }} <span class="text-sm font-medium text-slate-500">pesanan</span></p>
                <p class="mt-3 text-xs font-medium text-rose-600">Segera cek pembayaran</p>
            </section>
            <section class="rounded-xl border border-violet-100 bg-violet-50/60 p-4">
                <p class="text-xs text-slate-500">Dalam pengiriman</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ $shippingOrderCount }} <span class="text-sm font-medium text-slate-500">pesanan</span></p>
                <p class="mt-3 text-xs font-medium text-violet-600">Pastikan resi terinput</p>
            </section>
            <section class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4">
                <p class="text-xs text-slate-500">Nilai transaksi</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">Rp {{ number_format($salesTotal, 0, ',', '.') }}</p>
                <p class="mt-3 text-xs font-medium text-emerald-600">Pesanan yang telah dibayar</p>
            </section>
        </div>
    </div>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 lg:px-6">
            <div>
                <h2 class="font-bold text-slate-900">Pesanan Masuk & Verifikasi Pengiriman</h2>
                <p class="mt-1 text-xs text-slate-500">Gunakan chat untuk konfirmasi, kemudian proses dan masukkan resi ekspedisi.</p>
            </div>
            <a href="{{ route('seller.orders.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 hover:border-[#1257d6] hover:text-[#1257d6]">Lihat semua pesanan</a>
        </div>

        @forelse ($recentOrders as $order)
            @php $item = $order->orderItems->first(); @endphp
            <a href="{{ route('orders.show', $order) }}" class="grid gap-3 border-b border-slate-100 px-5 py-4 transition hover:bg-slate-50 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,0.75fr)_minmax(0,0.7fr)_auto] lg:items-center lg:px-6">
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-[#1257d6]">{{ $order->order_number ?? '#'.$order->id }}</p>
                    <p class="mt-1 truncate font-semibold text-slate-800">{{ $item?->product?->name ?? 'Produk tidak tersedia' }}</p>
                    <p class="mt-1 text-xs text-slate-500">Pembeli: {{ $order->buyer->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Nilai pesanan</p>
                    <p class="mt-1 text-sm font-bold text-slate-800">Rp {{ number_format($order->total, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Pengiriman</p>
                    <p class="mt-1 text-sm font-medium text-slate-700">{{ $order->shipping_method?->value === 'cod' ? 'COD' : strtoupper($order->shipping?->courier ?? 'Ekspedisi') }}</p>
                </div>
                <span class="justify-self-start rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-[#1257d6] lg:justify-self-end">{{ str_replace('_', ' ', $order->status->value) }}</span>
            </a>
        @empty
            <div class="p-10 text-center">
                <p class="font-semibold text-slate-700">Belum ada pesanan masuk</p>
                <p class="mt-1 text-sm text-slate-500">Produk yang aktif akan muncul di marketplace dan dapat dibeli pelanggan.</p>
                <a href="{{ route('seller.products.create') }}" class="mt-4 inline-flex rounded-lg bg-[#1257d6] px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Tambah produk pertama</a>
            </div>
        @endforelse
    </section>
@endsection
