@extends('layouts.admin')

@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
    <div class="grid gap-4 sm:grid-cols-3">
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
    </div>
@endsection