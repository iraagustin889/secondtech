@extends('layouts.admin')

@section('title', $product->name)
@section('heading', 'Detail Produk')

@section('content')
    <a href="{{ route('admin.products.index') }}" class="mb-4 inline-block text-sm text-blue-600 hover:underline">&larr; Kembali ke daftar</a>

    <div class="rounded-lg border bg-white p-6">
        <div class="flex flex-col gap-6 md:flex-row">
            <div class="md:w-1/3">
                @if ($product->image)
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                         class="w-full rounded border object-cover">
                @else
                    <div class="flex h-48 items-center justify-center rounded border bg-gray-50 text-sm text-gray-400">
                        Tidak ada foto
                    </div>
                @endif
            </div>

            <div class="flex-1">
                <h2 class="text-xl font-bold">{{ $product->name }}</h2>
                <p class="mt-1 text-2xl font-bold text-blue-600">Rp {{ number_format($product->price, 0, ',', '.') }}</p>

                <dl class="mt-4 grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-gray-500">Penjual</dt><dd>{{ $product->seller->name ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Kategori</dt><dd>{{ $product->category->name ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Kondisi</dt><dd>{{ $product->condition }}</dd></div>
                    <div><dt class="text-gray-500">Berat</dt><dd>{{ $product->weight }} gram</dd></div>
                    <div><dt class="text-gray-500">Lokasi</dt><dd>{{ $product->location }}</dd></div>
                    <div><dt class="text-gray-500">Stok</dt><dd>{{ $product->stock }}</dd></div>
                    <div><dt class="text-gray-500">Status</dt><dd>{{ $product->status }}</dd></div>
                    <div><dt class="text-gray-500">Dibuat</dt><dd>{{ $product->created_at?->format('d M Y H:i') }}</dd></div>
                </dl>

                <div class="mt-4">
                    <p class="text-sm text-gray-500">Deskripsi</p>
                    <p class="mt-1 whitespace-pre-line text-sm">{{ $product->description ?: '-' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection