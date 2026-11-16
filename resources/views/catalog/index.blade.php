@extends('layouts.app')

@section('title', 'Katalog Produk - SECONDTECH')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Katalog Produk</h1>
        <p class="mt-1 text-gray-600">Temukan perangkat second-hand berkualitas dari penjual terverifikasi.</p>
    </div>

    {{-- Form filter (GET supaya URL bisa dibagikan dan pagination tetap membawa filter) --}}
    <form method="GET" action="{{ route('products.index') }}"
          class="mb-6 grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-2 xl:grid-cols-6">
        <div class="min-w-0 xl:col-span-2">
            <label for="q" class="block text-xs text-gray-500 mb-1">Cari nama produk</label>
            <input type="text" id="q" name="q" value="{{ $filters['q'] ?? '' }}"
                   placeholder="Contoh: laptop"
                   class="w-full border rounded px-2 py-1.5 text-sm">
        </div>

        <div class="min-w-0">
            <label for="category" class="block text-xs text-gray-500 mb-1">Kategori</label>
            <select id="category" name="category" class="w-full border rounded px-2 py-1.5 text-sm">
                <option value="">Semua</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}"
                        @selected(($filters['category'] ?? null) == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="min-w-0">
            <label for="condition" class="block text-xs text-gray-500 mb-1">Kondisi</label>
            <select id="condition" name="condition" class="w-full border rounded px-2 py-1.5 text-sm">
                <option value="">Semua</option>
                @foreach ($conditions as $condition)
                    <option value="{{ $condition }}"
                        @selected(($filters['condition'] ?? null) === $condition)>
                        {{ $condition }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="min-w-0">
            <label for="min_price" class="block text-xs text-gray-500 mb-1">Harga min (Rp)</label>
            <input type="number" id="min_price" name="min_price" min="0"
                   value="{{ $filters['min_price'] ?? '' }}"
                   class="w-full border rounded px-2 py-1.5 text-sm">
        </div>

        <div class="min-w-0">
            <label for="max_price" class="block text-xs text-gray-500 mb-1">Harga maks (Rp)</label>
            <input type="number" id="max_price" name="max_price" min="0"
                   value="{{ $filters['max_price'] ?? '' }}"
                   class="w-full border rounded px-2 py-1.5 text-sm">
        </div>

        <div class="min-w-0 xl:col-span-3">
            <label for="location" class="block text-xs text-gray-500 mb-1">Lokasi</label>
            <input type="text" id="location" name="location" value="{{ $filters['location'] ?? '' }}"
                   placeholder="Contoh: Banyuwangi"
                   class="w-full border rounded px-2 py-1.5 text-sm">
        </div>

        <div class="flex items-end gap-2 md:col-span-2 xl:col-span-3">
            <button type="submit" class="bg-blue-600 text-white px-4 py-1.5 rounded text-sm hover:bg-blue-700">
                Terapkan
            </button>
            <a href="{{ route('products.index') }}" class="px-4 py-1.5 text-sm border rounded hover:bg-gray-100">
                Reset
            </a>
        </div>
    </form>

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded border border-red-200">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Grid produk --}}
    @if ($products->isEmpty())
        <div class="bg-white border rounded p-10 text-center text-gray-500">
            <p class="font-semibold">Produk tidak ditemukan</p>
            <p class="text-sm mt-1">Coba ubah kata kunci atau longgarkan filter.</p>
        </div>
    @else
        <p class="text-sm text-gray-500 mb-3">{{ $products->total() }} produk ditemukan</p>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($products as $product)
                <a href="{{ route('products.show', $product) }}"
                   class="bg-white border rounded overflow-hidden flex flex-col hover:shadow">
                    @if ($product->image)
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                             class="w-full h-40 object-cover">
                    @else
                        @php
                            $categoryVisual = [
                                'Laptop' => ['💻', 'from-sky-100 to-blue-50'],
                                'Smartphone' => ['📱', 'from-violet-100 to-purple-50'],
                                'Tablet' => ['📱', 'from-indigo-100 to-blue-50'],
                                'Kamera' => ['📷', 'from-amber-100 to-orange-50'],
                                'Konsol Game' => ['🎮', 'from-emerald-100 to-teal-50'],
                                'Audio & Headphone' => ['🎧', 'from-rose-100 to-pink-50'],
                                'Aksesoris' => ['⌨️', 'from-slate-100 to-gray-50'],
                            ][$product->category?->name] ?? ['📦', 'from-gray-100 to-slate-50'];
                        @endphp
                        <div class="flex h-40 w-full flex-col items-center justify-center bg-gradient-to-br {{ $categoryVisual[1] }} text-center">
                            <span class="text-5xl" aria-hidden="true">{{ $categoryVisual[0] }}</span>
                            <span class="mt-2 text-xs font-medium text-gray-600">{{ $product->category?->name }}</span>
                        </div>
                    @endif

                    <div class="p-3 flex-1 flex flex-col gap-1">
                        <h2 class="font-semibold text-sm line-clamp-2">{{ $product->name }}</h2>
                        <p class="text-blue-600 font-bold">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500">{{ $product->category?->name }}</p>
                        <p class="text-xs text-gray-500">{{ $product->seller?->name }}</p>
                        <div class="mt-auto pt-2 flex items-center justify-between text-xs text-gray-600">
                            <span class="px-2 py-0.5 bg-gray-100 rounded">{{ $product->condition }}</span>
                            <span>{{ $product->location }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $products->links() }}
        </div>
    @endif
@endsection
