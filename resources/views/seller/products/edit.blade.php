@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Edit Produk</h1>

    @if ($errors->any())
        <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('seller.products.update', $product) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block mb-1 font-medium">Nama produk</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}"
                   class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block mb-1 font-medium">Kategori</label>
            <select name="category_id" class="w-full border rounded px-3 py-2">
                <option value="">-- Pilih kategori --</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>
                        {{ $category->name }}{{ $category->is_active ? '' : ' (nonaktif)' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block mb-1 font-medium">Harga (Rp)</label>
            <input type="number" name="price" value="{{ old('price', $product->price) }}"
                   class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block mb-1 font-medium">Kondisi</label>
            <select name="condition" class="w-full border rounded px-3 py-2">
                <option value="">-- Pilih kondisi --</option>
                @foreach ($conditions as $condition)
                    <option value="{{ $condition }}" @selected(old('condition', $product->condition) == $condition)>
                        {{ $condition }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block mb-1 font-medium">Berat (gram)</label>
            <input type="number" name="weight" value="{{ old('weight', $product->weight) }}"
                   class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block mb-1 font-medium">Lokasi (kota)</label>
            <input type="text" name="location" value="{{ old('location', $product->location) }}"
                   class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block mb-1 font-medium">Stok</label>
            <input type="number" name="stock" value="{{ old('stock', $product->stock) }}"
                   class="w-full border rounded px-3 py-2">
        </div>

        {{-- Foto: tampilkan yang sekarang, upload baru bersifat opsional --}}
        <div>
            <label class="block mb-1 font-medium">Foto produk (maks 2 MB)</label>
            @if ($product->image)
                <img src="{{ asset('storage/' . $product->image) }}"
                     alt="{{ $product->name }}"
                     class="w-32 h-32 object-cover rounded mb-2">
                <p class="text-sm text-gray-600 mb-2">Kosongkan kalau tidak ingin mengganti foto.</p>
            @endif
            <input type="file" name="image" accept="image/*"
                   class="w-full border rounded px-3 py-2">
        </div>

        <div>
            <label class="block mb-1 font-medium">Deskripsi (opsional)</label>
            <textarea name="description" rows="4"
                      class="w-full border rounded px-3 py-2">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Simpan Perubahan</button>
            <a href="{{ route('seller.products.index') }}" class="px-4 py-2 underline">Batal</a>
        </div>
    </form>
</div>
@endsection
