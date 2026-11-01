@extends('layouts.admin')

@section('title', 'Produk')
@section('heading', 'Daftar Produk')

@section('content')
    <form method="GET" action="{{ route('admin.products.index') }}" class="mb-4 flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ $search }}"
               placeholder="Cari nama produk..."
               class="w-full max-w-sm rounded border px-3 py-2 text-sm">
        <input type="text" name="status" value="{{ $status }}"
               placeholder="Status (mis. active)"
               class="w-40 rounded border px-3 py-2 text-sm">
        <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">Cari</button>
        @if ($search || $status)
            <a href="{{ route('admin.products.index') }}" class="rounded border px-4 py-2 text-sm hover:bg-gray-50">Reset</a>
        @endif
    </form>

    <div class="overflow-x-auto rounded-lg border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-3">Produk</th>
                    <th class="px-4 py-3">Penjual</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Harga</th>
                    <th class="px-4 py-3">Stok</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($products as $product)
                    <tr>
                        <td class="px-4 py-3">{{ $product->name }}</td>
                        <td class="px-4 py-3">{{ $product->seller->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $product->category->name ?? '-' }}</td>
                        <td class="px-4 py-3">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ $product->stock }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded px-2 py-1 text-xs {{ $product->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-700' }}">
                                {{ $product->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.products.show', $product) }}" class="text-blue-600 hover:underline">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-gray-500">Produk tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
@endsection