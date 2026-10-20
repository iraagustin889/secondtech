@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Tambah Produk</h1>

    {{-- Pesan error validasi --}}
    @if ($errors->any())
        <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('seller.products.store') }}" class="space-y-4">
        @csrf

        {{-- Nama --}}
        <div>
            <label class="block mb-1 font-medium">Nama produk</label>
            <input type="text" name="name" value="{{ old('name') }}"
                   class="w-full border rounded px-3 py-2">
        </div>

        {{-- Kategori: hanya kategori aktif yang dikirim dari controller --}}
        <div>
            <label class="block mb-1 font-medium">Kategori</label>
            <select name="category_id" class="w-full border rounded px-3 py-2">
                <option value="">-- Pilih kategori --</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Harga --}}
        <div>
            <label class="block mb-1 font-medium">Harga (Rp)</label>
            <input type="number" name="price" value="{{ old('price') }}"
                   class="w-full border rounded px-3 py-2">
        </div>

        {{-- Kondisi --}}
        <div>
            <label class="block mb-1 font-medium">Kondisi</label>
            <select name="condition" class="w-full border rounded px-3 py-2">
                <option value="">-- Pilih kondisi --</option>
                @foreach ($conditions as $condition)
                    <option value="{{ $condition }}" @selected(old('condition') == $condition)>
                        {{ $condition }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Berat --}}
        <div>
            <label class="block mb-1 font-medium">Berat (gram)</label>
            <input type="number" name="weight" value="{{ old('weight') }}"
                   class="w-full border rounded px-3 py-2">
        </div>

        {{-- Lokasi --}}
        <div>
            <label class="block mb-1 font-medium">Lokasi (kota)</label>
            <input type="text" name="location" value="{{ old('location') }}"
                   class="w-full border rounded px-3 py-2">
        </div>

        {{-- Stok --}}
        <div>
            <label class="block mb-1 font-medium">Stok</label>
            <input type="number" name="stock" value="{{ old('stock', 1) }}"
                   class="w-full border rounded px-3 py-2">
        </div>

        {{-- Deskripsi --}}
        <div>
            <label class="block mb-1 font-medium">Deskripsi (opsional)</label>
            <textarea name="description" rows="4"
                      class="w-full border rounded px-3 py-2">{{ old('description') }}</textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
            <a href="{{ route('seller.products.index') }}" class="px-4 py-2 underline">Batal</a>
        </div>
    </form>
</div>
@endsection
