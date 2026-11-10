@extends('layouts.app')

@section('title', $product->name . ' - SECONDTECH')

@section('content')
    <a href="{{ route('products.index') }}" class="text-sm text-blue-600 hover:underline">&larr; Kembali ke katalog</a>

    @if ($product->status !== 'active')
        <div class="mt-3 p-3 bg-yellow-50 text-yellow-800 text-sm rounded border border-yellow-200">
            Produk ini tidak tampil di katalog (status: {{ $product->status }}). Hanya Anda yang bisa melihat halaman ini.
        </div>
    @endif

    <div class="mt-4 grid gap-6 md:grid-cols-2">
        {{-- Foto --}}
        <div class="bg-white border rounded overflow-hidden">
            @if ($product->image)
                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                     class="w-full max-h-96 object-cover">
            @else
                <div class="w-full h-64 bg-gray-100 flex items-center justify-center text-sm text-gray-400">
                    Tanpa foto
                </div>
            @endif
        </div>

        {{-- Info utama --}}
        <div class="flex flex-col gap-3">
            <h1 class="text-2xl font-bold">{{ $product->name }}</h1>
            <p class="text-3xl font-bold text-blue-600">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                        @if ($reviewCount > 0)
                <p class="text-sm text-yellow-600">★ {{ $avgRating }} <span class="text-gray-500">({{ $reviewCount }} ulasan)</span></p>
            @else
                <p class="text-sm text-gray-500">Belum ada ulasan</p>
            @endif

            <div class="flex flex-wrap gap-2 text-xs">
                <span class="px-2 py-0.5 bg-gray-100 rounded">{{ $product->category?->name }}</span>
                <span class="px-2 py-0.5 bg-gray-100 rounded">{{ $product->condition }}</span>
            </div>

            {{-- Area aksi pembeli --}}
            <div class="mt-2">
                @if ($isOwner)
                    <div class="p-3 bg-blue-50 text-blue-800 text-sm rounded border border-blue-200">
                        Ini produk Anda.
                        <a href="{{ route('seller.products.edit', $product) }}" class="underline">Edit produk</a>
                    </div>
                @elseif (! $product->isPurchasable())
                    <div class="p-3 bg-yellow-50 text-yellow-800 text-sm rounded border border-yellow-200">
                        Produk sedang tidak tersedia.
                    </div>
                @else
                    @auth
                        <a id="buy-button" href="{{ route('checkout.create', $product) }}"
                           class="inline-flex px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Beli sekarang
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Login untuk membeli
                        </a>
                    @endauth
                @endif
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 md:grid-cols-3">
        {{-- Deskripsi dan spesifikasi --}}
        <div class="md:col-span-2 bg-white border rounded p-4">
            <h2 class="font-semibold mb-2">Deskripsi</h2>
            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $product->description ?: 'Penjual belum menulis deskripsi.' }}</p>

            <h2 class="font-semibold mt-5 mb-2">Spesifikasi</h2>
            <table class="text-sm w-full">
                <tbody class="divide-y">
                    <tr><td class="py-1.5 text-gray-500 w-40">Kategori</td><td>{{ $product->category?->name }}</td></tr>
                    <tr><td class="py-1.5 text-gray-500">Kondisi</td><td>{{ $product->condition }}</td></tr>
                    <tr><td class="py-1.5 text-gray-500">Berat</td><td>{{ number_format($product->weight, 0, ',', '.') }} gram</td></tr>
                    <tr><td class="py-1.5 text-gray-500">Stok</td><td>{{ $product->stock }}</td></tr>
                    <tr><td class="py-1.5 text-gray-500">Lokasi</td><td>{{ $product->location }}</td></tr>
                </tbody>
            </table>
        </div>

        {{-- Info penjual --}}
        <div class="bg-white border rounded p-4 h-fit">
            <h2 class="font-semibold mb-2">Penjual</h2>
            <p class="font-medium">{{ $product->seller->name }}</p>
            @if ($product->seller->location)
                <p class="text-sm text-gray-500">{{ $product->seller->location }}</p>
            @endif
            <p class="text-xs text-gray-400 mt-1">Bergabung {{ $product->seller->created_at->translatedFormat('F Y') }}</p>
                        @if ($sellerReviewCount > 0)
                <p class="text-xs text-yellow-600 mt-1">★ {{ $sellerRating }} ({{ $sellerReviewCount }} ulasan penjual)</p>
            @endif
        </div>
    </div>

        {{-- Ulasan pembeli --}}
    <div class="mt-6 bg-white border rounded p-4">
        <h2 class="font-semibold mb-2">Ulasan Pembeli</h2>
        @forelse ($product->reviews->sortByDesc('created_at') as $review)
            <div class="border-t pt-3 mt-3">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium">{{ $review->buyer->name ?? 'Pengguna' }}</p>
                    <p class="text-xs text-gray-400">{{ $review->created_at->format('d M Y') }}</p>
                </div>
                <p class="text-yellow-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</p>
                @if ($review->comment)
                    <p class="text-sm text-gray-700 mt-1">{{ $review->comment }}</p>
                @endif
            </div>
        @empty
            <p class="text-sm text-gray-500">Belum ada ulasan.</p>
        @endforelse
    </div>
@endsection
