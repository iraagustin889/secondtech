@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <div class="rounded-lg bg-white p-5 shadow-sm border">
            <p class="text-sm text-gray-500">Total pengguna</p>
            <p class="mt-1 text-3xl font-bold">{{ $totalUsers }}</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm border">
            <p class="text-sm text-gray-500">Pengguna aktif</p>
            <p class="mt-1 text-3xl font-bold text-green-600">{{ $activeUsers }}</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm border">
            <p class="text-sm text-gray-500">Pengguna nonaktif</p>
            <p class="mt-1 text-3xl font-bold text-red-600">{{ $inactiveUsers }}</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm border">
            <p class="text-sm text-gray-500">Total pesanan</p>
            <p class="mt-1 text-3xl font-bold">{{ $totalOrders }}</p>
        </div>
        <a href="{{ route('admin.payments.index') }}" class="rounded-lg border bg-white p-5 shadow-sm transition hover:border-blue-400 hover:shadow">
            <p class="text-sm text-gray-500">Menunggu verifikasi pembayaran</p>
            <p class="mt-1 text-3xl font-bold text-amber-600">{{ $pendingPayments }}</p>
            <p class="mt-2 text-sm text-blue-700">Buka antrean pembayaran →</p>
        </a>
        <div class="rounded-lg bg-white p-5 shadow-sm border">
            <p class="text-sm text-gray-500">Pesanan aktif</p>
            <p class="mt-1 text-3xl font-bold text-blue-600">{{ $activeOrders }}</p>
        </div>
        <div class="rounded-lg bg-white p-5 shadow-sm border">
            <p class="text-sm text-gray-500">Nilai transaksi berhasil</p>
            <p class="mt-1 text-3xl font-bold text-green-600">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
        </div>
    </div>
@endsection
