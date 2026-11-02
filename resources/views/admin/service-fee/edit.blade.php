@extends('layouts.admin')

@section('title', 'Biaya Layanan')
@section('heading', 'Pengaturan Biaya Layanan')

@section('content')
    <div class="mb-6 rounded-lg border bg-white p-6">
        <p class="text-sm text-gray-500">Biaya layanan aktif saat ini</p>
        @if ($active)
            <p class="mt-1 text-2xl font-bold text-blue-600">
                @if ($active->type === 'percentage')
                    {{ rtrim(rtrim(number_format($active->value, 2, ',', '.'), '0'), ',') }}%
                @else
                    Rp {{ number_format($active->value, 0, ',', '.') }}
                @endif
            </p>
        @else
            <p class="mt-1 text-lg text-gray-500">Belum diatur (biaya layanan = Rp 0)</p>
        @endif
    </div>

    <form method="POST" action="{{ route('admin.service-fee.update') }}"
          class="mb-6 max-w-lg rounded-lg border bg-white p-6">
        @csrf
        @method('PUT')

        <label class="block text-sm font-medium">Jenis biaya</label>
        <select name="type" class="mt-1 w-full rounded border px-3 py-2 text-sm">
            <option value="">-- Pilih jenis --</option>
            <option value="nominal" @selected(old('type', $active->type ?? '') === 'nominal')>Nominal (Rp)</option>
            <option value="percentage" @selected(old('type', $active->type ?? '') === 'percentage')>Persentase (%)</option>
        </select>
        @error('type')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror

        <label class="mt-4 block text-sm font-medium">Nilai</label>
        <input type="number" step="0.01" min="0" name="value"
               value="{{ old('value', $active->value ?? '') }}"
               class="mt-1 w-full rounded border px-3 py-2 text-sm">
        @error('value')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
        <p class="mt-1 text-xs text-gray-500">Nominal: angka rupiah. Persentase: 0 sampai 100.</p>

        <button type="submit" class="mt-4 rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
            Simpan
        </button>
    </form>

    <div class="overflow-x-auto rounded-lg border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-3">Dibuat</th>
                    <th class="px-4 py-3">Jenis</th>
                    <th class="px-4 py-3">Nilai</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($history as $item)
                    <tr>
                        <td class="px-4 py-3">{{ $item->created_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $item->type === 'percentage' ? 'Persentase' : 'Nominal' }}</td>
                        <td class="px-4 py-3">{{ $item->value + 0 }}</td>
                        <td class="px-4 py-3">{{ $item->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">Belum ada riwayat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection