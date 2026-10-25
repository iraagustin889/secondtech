{{-- Pakai layout yang sama dengan halaman lain --}}
@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Produk Saya</h1>

    {{-- Tombol menuju form tambah produk --}}
    <a href="{{ route('seller.products.create') }}"
       class="inline-block mb-4 bg-blue-600 text-white px-4 py-2 rounded">
        + Tambah Produk
    </a>

    {{-- Pesan sukses (dipakai nanti setelah tambah/edit/hapus) --}}
    @if (session('success'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Kalau belum punya produk --}}
    @if ($products->isEmpty())
        <p class="text-gray-600">Belum ada produk.</p>
    @else
        <table class="w-full border text-left">
            <thead class="bg-gray-100">
                <tr>
                    {{-- BARU: kolom foto --}}
                    <th class="p-2">Foto</th>
                    <th class="p-2">Nama</th>
                    <th class="p-2">Kategori</th>
                    <th class="p-2">Harga</th>
                    <th class="p-2">Kondisi</th>
                    <th class="p-2">Status</th>
                    <th class="p-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                    <tr class="border-t">
                        {{-- BARU: kalau ada foto tampilkan, kalau tidak tampilkan tanda strip --}}
                        <td class="p-2">
                            @if ($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}"
                                     alt="{{ $product->name }}"
                                     class="w-16 h-16 object-cover rounded">
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="p-2">{{ $product->name }}</td>
                        <td class="p-2">{{ $product->category->name }}</td>
                        {{-- number_format: 1500000 jadi 1.500.000 --}}
                        <td class="p-2">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                        <td class="p-2">{{ $product->condition }}</td>
                        <td class="p-2">{{ $product->status }}</td>
                        <td class="p-2">
                            <a href="{{ route('seller.products.edit', $product) }}" class="text-blue-600 underline">Edit</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
@endsection
