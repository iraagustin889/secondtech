@extends('layouts.app')

@section('title', 'Lacak '.$order->order_number)

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('orders.show', $order) }}" class="text-sm text-blue-700 underline">← Kembali ke pesanan</a>
        <h1 class="mt-4 text-2xl font-bold">Lacak pengiriman</h1>
        <div class="mt-6 rounded-xl border bg-white p-5"><p class="text-sm text-gray-500">{{ strtoupper($shipping->courier) }} · {{ $shipping->service }}</p><p class="mt-1 text-xl font-bold">{{ $shipping->resi }}</p><p class="mt-4 rounded bg-blue-50 p-3 text-blue-800">{{ $tracking['status'] }}</p></div>
        <section class="mt-6 rounded-xl border bg-white p-5"><h2 class="font-bold">Riwayat pelacakan</h2>@forelse ($tracking['events'] as $event)<pre class="mt-3 overflow-x-auto rounded bg-gray-50 p-3 text-xs text-gray-700">{{ json_encode($event, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>@empty<p class="mt-3 text-sm text-gray-600">Belum ada detail pergerakan dari layanan ekspedisi.</p>@endforelse</section>
    </div>
@endsection
