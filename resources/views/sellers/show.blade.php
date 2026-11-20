@extends('layouts.app')

@section('title', 'Profil '.$seller->name.' - SECONDTECH')

@section('content')
    <a href="{{ route('products.index') }}" class="text-sm text-blue-700 hover:underline">&larr; Kembali ke katalog</a>

    <section class="mt-4 rounded-xl border bg-white p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-sm text-gray-500">Profil penjual</p>
                <h1 class="mt-1 text-2xl font-bold">{{ $seller->name }}</h1>
                <p class="mt-2 text-sm text-gray-600">{{ $seller->location ?: 'Lokasi belum dilengkapi' }} · Bergabung {{ $seller->created_at->translatedFormat('F Y') }}</p>
            </div>
            <div class="rounded-lg bg-blue-50 px-4 py-3 text-sm text-blue-900">
                <p><strong>{{ $seller->active_products_count }}</strong> produk aktif</p>
                <p class="mt-1"><strong>{{ number_format((float) ($seller->reviews_received_avg_rating ?? 0), 1) }} ★</strong> dari {{ $seller->reviews_received_count }} ulasan</p>
            </div>
        </div>
        <p class="mt-5 max-w-3xl whitespace-pre-line text-gray-700">{{ $seller->seller_bio ?: 'Penjual belum menambahkan deskripsi toko.' }}</p>
    </section>

    <section class="mt-8">
        <h2 class="text-xl font-bold">Produk dari {{ $seller->name }}</h2>
        @if ($products->isEmpty())
            <p class="mt-3 rounded-xl border border-dashed bg-white p-8 text-center text-gray-600">Belum ada produk aktif dari penjual ini.</p>
        @else
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    <a href="{{ route('products.show', $product) }}" class="overflow-hidden rounded-xl border bg-white transition hover:border-blue-400 hover:shadow-sm">
                        @if ($product->image)
                            <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}" class="h-40 w-full object-cover">
                        @else
                            <div class="flex h-40 items-center justify-center bg-gray-100 text-sm text-gray-400">Tanpa foto</div>
                        @endif
                        <div class="p-4"><p class="line-clamp-2 font-semibold">{{ $product->name }}</p><p class="mt-2 font-bold text-blue-700">Rp {{ number_format($product->price, 0, ',', '.') }}</p><p class="mt-1 text-xs text-gray-500">Stok {{ $product->stock }} · {{ $product->condition }}</p></div>
                    </a>
                @endforeach
            </div>
            <div class="mt-6">{{ $products->links() }}</div>
        @endif
    </section>
@endsection
