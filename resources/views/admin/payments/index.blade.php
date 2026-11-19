@extends('layouts.admin')

@section('title', 'Verifikasi Pembayaran')
@section('heading', 'Verifikasi Pembayaran')

@section('content')
    <p class="mb-4 text-sm text-gray-600">Daftar ini hanya menampilkan pembayaran transfer yang sudah dikirim pembeli dan masih menunggu keputusan admin.</p>

    <div class="overflow-x-auto rounded-lg border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-3">Pesanan</th>
                    <th class="px-4 py-3">Pembeli</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Referensi</th>
                    <th class="px-4 py-3">Dikirim</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($payments as $payment)
                    <tr>
                        <td class="px-4 py-3">
                            <a href="{{ route('orders.show', $payment->order) }}" class="font-medium text-blue-700 hover:underline">
                                {{ $payment->order->order_number ?? '#'.$payment->order->id }}
                            </a>
                        </td>
                        <td class="px-4 py-3">{{ $payment->order->buyer->name ?? '-' }}<br><span class="text-xs text-gray-500">{{ $payment->order->buyer->email ?? '-' }}</span></td>
                        <td class="px-4 py-3">Rp {{ number_format($payment->order->total, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ $payment->reference }}</td>
                        <td class="px-4 py-3">{{ $payment->submitted_at?->format('d M Y H:i') ?? '-' }}</td>
                        <td class="min-w-72 px-4 py-3">
                            <div class="flex flex-col gap-2">
                                <form method="POST" action="{{ route('admin.orders.payment.confirm', $payment->order) }}">
                                    @csrf
                                    <button class="rounded bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700">Setujui pembayaran</button>
                                </form>
                                <form method="POST" action="{{ route('admin.orders.payment.reject', $payment->order) }}" class="flex gap-2">
                                    @csrf
                                    <input name="failure_reason" required minlength="5" maxlength="500" class="min-w-0 flex-1 rounded border px-2 py-1 text-xs" placeholder="Alasan penolakan">
                                    <button class="rounded border border-red-300 px-3 py-1 text-xs font-medium text-red-700 hover:bg-red-50">Tolak</button>
                                </form>
                                @error('failure_reason')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Tidak ada pembayaran yang menunggu verifikasi.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $payments->links() }}
    </div>
@endsection
