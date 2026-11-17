@extends('layouts.admin')

@section('title', 'Sengketa & Pengembalian')
@section('heading', 'Sengketa & Pengembalian')

@section('content')
    <div class="mb-5 rounded-lg border border-blue-100 bg-blue-50 p-4 text-sm text-blue-900">
        Dana pesanan tetap ditahan oleh rekber lokal sampai pengajuan disetujui atau ditolak.
    </div>

    <div class="overflow-x-auto rounded-lg border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr><th class="px-4 py-3">Pesanan</th><th class="px-4 py-3">Pembeli</th><th class="px-4 py-3">Alasan</th><th class="px-4 py-3">Status</th><th class="px-4 py-3">Tindakan</th></tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($refundRequests as $refundRequest)
                    <tr class="align-top">
                        <td class="px-4 py-3"><a class="font-medium text-blue-700 hover:underline" href="{{ route('orders.show', $refundRequest->order) }}">{{ $refundRequest->order->order_number }}</a><p class="mt-1 text-xs text-gray-500">{{ $refundRequest->created_at->format('d M Y H:i') }}</p></td>
                        <td class="px-4 py-3">{{ $refundRequest->buyer->name }}</td>
                        <td class="max-w-sm px-4 py-3"><p class="font-medium">{{ $refundRequest->reason }}</p><p class="mt-1 whitespace-pre-line text-gray-600">{{ $refundRequest->description }}</p>@if($refundRequest->resolution_note)<p class="mt-2 text-xs text-gray-500">Catatan admin: {{ $refundRequest->resolution_note }}</p>@endif</td>
                        <td class="px-4 py-3"><span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium">{{ str_replace('_', ' ', $refundRequest->status->value) }}</span></td>
                        <td class="px-4 py-3">
                            @if ($refundRequest->status->value === 'requested')
                                <div class="flex min-w-64 flex-col gap-2">
                                    <form method="POST" action="{{ route('admin.refund-requests.approve', $refundRequest) }}" class="flex gap-2">@csrf<textarea name="resolution_note" rows="2" class="min-w-0 flex-1 rounded border px-2 py-1 text-xs" placeholder="Catatan (opsional)"></textarea><button class="rounded bg-green-600 px-3 py-1 text-xs font-semibold text-white">Setujui</button></form>
                                    <form method="POST" action="{{ route('admin.refund-requests.reject', $refundRequest) }}" class="flex gap-2">@csrf<textarea name="resolution_note" rows="2" class="min-w-0 flex-1 rounded border px-2 py-1 text-xs" placeholder="Alasan penolakan"></textarea><button class="rounded bg-red-600 px-3 py-1 text-xs font-semibold text-white">Tolak</button></form>
                                </div>
                            @else
                                <span class="text-xs text-gray-500">Diselesaikan {{ $refundRequest->resolved_at?->format('d M Y H:i') }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-gray-500">Belum ada pengajuan pengembalian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-5">{{ $refundRequests->links() }}</div>
@endsection
