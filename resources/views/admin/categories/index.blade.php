{{-- Pakai layout yang sama dengan halaman lain --}}
@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Kelola Kategori</h1>

    {{-- Pesan sukses setelah tambah/ubah status --}}
    @if (session('success'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Pesan error validasi --}}
    @if ($errors->any())
        <div class="mb-4 p-3 rounded bg-red-100 text-red-800">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- Form tambah kategori --}}
    <form method="POST" action="{{ route('admin.categories.store') }}" class="flex gap-2 mb-6">
        @csrf
        <input type="text" name="name" value="{{ old('name') }}"
               placeholder="Nama kategori baru"
               class="flex-1 border rounded px-3 py-2">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
            Tambah
        </button>
    </form>

    {{-- Tabel daftar kategori --}}
    <table class="w-full border text-left">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">Nama</th>
                <th class="p-2">Jumlah Produk</th>
                <th class="p-2">Status</th>
                <th class="p-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($categories as $category)
                <tr class="border-t">
                    <td class="p-2">{{ $category->name }}</td>
                    <td class="p-2">{{ $category->products_count }}</td>
                    <td class="p-2">
                        @if ($category->is_active)
                            <span class="text-green-700">Aktif</span>
                        @else
                            <span class="text-red-700">Nonaktif</span>
                        @endif
                    </td>
                    <td class="p-2">
                        {{-- Form kecil untuk ubah status (pakai PATCH) --}}
                        <form method="POST" action="{{ route('admin.categories.toggle', $category) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-blue-600 underline">
                                {{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
