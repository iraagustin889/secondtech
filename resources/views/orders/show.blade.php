@extends('layouts.app')

@section('title', 'Pesanan '.$order->order_number)

@section('content')
    @php
        $viewerIsBuyer = auth()->id() === $order->buyer_id;
        $viewerIsSeller = $order->orderItems->contains(fn ($item) => $item->product?->user_id === auth()->id());
        $viewerIsAdmin = auth()->user()->isAdmin();
        $paymentMethodLabel = $order->payment?->methodLabel() ?? '-';
    @endphp

    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div><p class="text-sm text-gray-500">Pesanan</p><h1 class="text-2xl font-bold">{{ $order->order_number ?? '#'.$order->id }}</h1><p class="mt-1 text-gray-600">Dibuat {{ $order->created_at->format('d M Y H:i') }}</p></div>
        <span class="rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-blue-800">{{ str_replace('_', ' ', $order->status->value) }}</span>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="space-y-6 lg:col-span-2">
            <div class="rounded-xl border bg-white p-5">
                <h2 class="font-bold">Produk</h2>
                @foreach ($order->orderItems as $item)
                    <div class="mt-4 flex justify-between gap-4 border-t pt-4"><div><p class="font-medium">{{ $item->product?->name ?? 'Produk tidak tersedia' }}</p><p class="text-sm text-gray-600">{{ $item->qty }} × Rp {{ number_format($item->price_snapshot, 0, ',', '.') }}</p></div><p class="font-semibold">Rp {{ number_format($item->qty * $item->price_snapshot, 0, ',', '.') }}</p></div>
                @endforeach
            </div>

            <div class="rounded-xl border bg-white p-5">
                <h2 class="font-bold">Pengiriman</h2>
                <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-gray-500">Metode</dt><dd class="font-medium">{{ $order->shipping_method?->value === 'cod' ? 'COD' : 'Ekspedisi' }}</dd></div>
                    <div><dt class="text-gray-500">Status</dt><dd class="font-medium">{{ str_replace('_', ' ', $order->shipping?->status->value ?? '-') }}</dd></div>
                    @if ($order->shipping?->method->value === 'expedition')
                        <div><dt class="text-gray-500">Layanan</dt><dd class="font-medium">{{ strtoupper($order->shipping->courier ?? '-') }} {{ $order->shipping->service }}</dd></div>
                        <div><dt class="text-gray-500">Resi</dt><dd class="font-medium">{{ $order->shipping->resi ?? 'Belum diinput penjual' }}</dd></div>
                        @if ($order->shipping?->resi)<div class="sm:col-span-2"><a class="text-blue-700 underline" href="{{ route('orders.tracking', $order) }}">Lacak pengiriman</a></div>@endif
                    @else
                        <div><dt class="text-gray-500">Lokasi COD</dt><dd class="font-medium">{{ $order->shipping?->cod_location ?? 'Menunggu persetujuan penjual' }}</dd></div>
                        <div><dt class="text-gray-500">Waktu COD</dt><dd class="font-medium">{{ $order->shipping?->cod_meeting_at?->format('d M Y H:i') ?? '-' }}</dd></div>
                    @endif
                </dl>
                @if ($viewerIsBuyer)
                    <div class="mt-5 border-t pt-4"><p class="text-sm font-medium">Tujuan</p><p class="mt-1 text-sm text-gray-700">{{ $order->recipient_name }} · {{ $order->recipient_phone }}<br>{{ $order->recipient_address }}<br>{{ $order->recipient_location }}</p></div>
                @endif
            </div>
        </section>

        <aside class="space-y-6">
            @if ($viewerIsBuyer || $viewerIsSeller)
                <form method="POST" action="{{ route('chats.start.order', $order) }}">
                    @csrf
                    <button class="w-full rounded border border-blue-600 px-4 py-2 font-medium text-blue-700 hover:bg-blue-50">
                        {{ $viewerIsBuyer ? 'Chat penjual' : 'Chat pembeli' }}
                    </button>
                </form>
            @endif

            <section class="rounded-xl border bg-white p-5">
                <h2 class="font-bold">Rincian pembayaran</h2>
                <dl class="mt-4 space-y-2 text-sm"><div class="flex justify-between"><dt>Subtotal</dt><dd>Rp {{ number_format($order->subtotal, 0, ',', '.') }}</dd></div><div class="flex justify-between"><dt>Ongkir</dt><dd>Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</dd></div><div class="flex justify-between"><dt>Biaya layanan</dt><dd>Rp {{ number_format($order->service_fee, 0, ',', '.') }}</dd></div><div class="flex justify-between border-t pt-2 text-base font-bold"><dt>Total</dt><dd>Rp {{ number_format($order->total, 0, ',', '.') }}</dd></div></dl>
                <p class="mt-4 text-sm text-gray-600">{{ $order->payment?->method === 'cod' ? 'Dibayar saat COD' : 'Metode: '.$paymentMethodLabel.' · Status: '.str_replace('_', ' ', $order->payment?->status->value ?? '-') }}</p>
                @if ($order->payment?->status?->value === 'paid' && ! $order->payment?->released_at)
                    <p class="mt-2 rounded bg-blue-50 p-2 text-sm text-blue-800">Dana ditahan aman oleh rekber lokal sampai pembeli mengonfirmasi pesanan selesai.</p>
                @elseif ($order->payment?->released_at)
                    <p class="mt-2 rounded bg-green-50 p-2 text-sm text-green-800">Transaksi selesai dan dana telah dilepas kepada penjual.</p>
                @elseif ($order->payment?->status?->value === 'refunded')
                    <p class="mt-2 rounded bg-amber-50 p-2 text-sm text-amber-800">Dana telah dikembalikan melalui alur pengembalian lokal.</p>
                @endif
                @if ($order->payment?->failure_reason)
                    <p class="mt-2 rounded bg-red-50 p-2 text-sm text-red-700">Alasan pembayaran ditolak: {{ $order->payment->failure_reason }}</p>
                @endif
            </section>

            @if (app()->environment(['local', 'testing']) && $viewerIsBuyer && $order->status->value === 'pending_payment' && $order->payment?->method !== 'cod')
                <section class="rounded-xl border bg-white p-5">
                    <h2 class="font-bold">SecondPay · Rekber lokal</h2>
                    <p class="mt-2 text-sm text-gray-600">{{ $paymentMethodLabel }} diproses otomatis untuk presentasi. Dana akan ditahan sampai pesanan selesai atau pengembalian disetujui.</p>
                    <form method="POST" action="{{ route('orders.payment.simulate', $order) }}" class="mt-3">
                        @csrf
                        <button class="w-full rounded bg-blue-600 px-4 py-2 font-medium text-white">Bayar Rp {{ number_format($order->total, 0, ',', '.') }}</button>
                    </form>
                </section>
            @endif

            @if ($viewerIsAdmin && $order->status->value === 'pending_payment' && $order->payment?->status->value === 'submitted')
                <form method="POST" action="{{ route('admin.orders.payment.confirm', $order) }}">@csrf<button class="w-full rounded bg-green-600 px-4 py-2 font-medium text-white">Konfirmasi pembayaran</button></form>
            @endif

            @if ($viewerIsBuyer && in_array($order->status->value, ['pending_payment', 'cod_requested', 'paid'], true))
                <section class="rounded-xl border border-red-200 bg-white p-5">
                    <h2 class="font-bold text-red-800">Batalkan pesanan</h2>
                    <p class="mt-1 text-sm text-gray-600">Pesanan hanya bisa dibatalkan sebelum penjual mulai memprosesnya. Bila sudah dibayar, dana lokal dikembalikan otomatis.</p>
                    <form method="POST" action="{{ route('orders.cancel', $order) }}" class="mt-3 space-y-3">@csrf
                        <select name="reason" class="w-full rounded border border-red-200 px-3 py-2 text-sm" required><option value="">Pilih alasan pembatalan</option><option>Berubah pikiran</option><option>Alamat atau data penerima salah</option><option>Ingin mengganti metode pembayaran</option><option>Penjual tidak merespons</option><option>Lainnya</option></select>
                        <textarea name="note" rows="2" class="w-full rounded border border-red-200 px-3 py-2 text-sm" placeholder="Catatan tambahan (opsional)"></textarea>
                        <button class="w-full rounded border border-red-300 px-4 py-2 font-medium text-red-700 hover:bg-red-50">Batalkan pesanan</button>
                    </form>
                </section>
            @endif

            @if ($order->status->value === 'cancelled')
                <section class="rounded-xl border border-slate-200 bg-white p-5 text-sm"><h2 class="font-bold">Pesanan dibatalkan</h2><p class="mt-2 text-slate-600">Alasan: {{ $order->cancellation_reason ?? '-' }}</p>@if($order->cancellation_note)<p class="mt-1 text-slate-600">Catatan: {{ $order->cancellation_note }}</p>@endif</section>
            @endif

            @if ($viewerIsSeller && $order->status->value === 'paid')
                <form method="POST" action="{{ route('seller.orders.process', $order) }}">@csrf<button class="w-full rounded bg-blue-600 px-4 py-2 font-medium text-white">Proses pesanan</button></form>
            @endif

            @if ($viewerIsSeller && $order->status->value === 'processing' && $order->shipping_method?->value === 'expedition')
                <section class="rounded-xl border bg-white p-5"><h2 class="font-bold">Masukkan nomor resi</h2><form method="POST" action="{{ route('seller.orders.ship', $order) }}" class="mt-3 space-y-3">@csrf<input name="resi" class="w-full rounded border px-3 py-2" placeholder="Nomor resi" required><button class="w-full rounded bg-blue-600 px-4 py-2 font-medium text-white">Tandai dikirim</button></form></section>
            @endif

            @if ($viewerIsSeller && $order->status->value === 'cod_requested')
                <section class="rounded-xl border bg-white p-5"><h2 class="font-bold">Setujui COD</h2><form method="POST" action="{{ route('seller.orders.cod.agree', $order) }}" class="mt-3 space-y-3">@csrf<textarea name="cod_location" class="w-full rounded border px-3 py-2" placeholder="Lokasi serah-terima" required></textarea><input type="datetime-local" name="cod_meeting_at" class="w-full rounded border px-3 py-2" required><button class="w-full rounded bg-blue-600 px-4 py-2 font-medium text-white">Simpan kesepakatan</button></form></section>
            @endif

            @if ($order->status->value === 'cod_agreed' && $viewerIsBuyer)
                <form method="POST" action="{{ route('orders.cod.confirm', $order) }}">@csrf<button class="w-full rounded bg-green-600 px-4 py-2 font-medium text-white">Saya sudah menerima & membayar COD</button></form>
            @endif
            @if ($order->status->value === 'cod_agreed' && $viewerIsSeller)
                <form method="POST" action="{{ route('seller.orders.cod.confirm', $order) }}">@csrf<button class="w-full rounded bg-green-600 px-4 py-2 font-medium text-white">Konfirmasi serah-terima COD</button></form>
            @endif
            @if ($viewerIsBuyer && in_array($order->status->value, ['shipped', 'received'], true))
                <form method="POST" action="{{ route('orders.received', $order) }}">
                    @csrf
                    <button class="w-full rounded bg-green-600 px-4 py-2 font-medium text-white">Barang sudah saya terima</button>
                </form>
            @endif
            @if ($viewerIsBuyer && in_array($order->status->value, ['shipped', 'received'], true) && ! $order->refundRequest)
                <section class="rounded-xl border border-amber-200 bg-amber-50 p-5">
                    <h2 class="font-bold text-amber-900">Ajukan pengembalian</h2>
                    <p class="mt-1 text-sm text-amber-800">Laporkan masalah sebelum mengonfirmasi barang diterima. Dana tetap ditahan selama admin memeriksa pengajuan.</p>
                    <form method="POST" action="{{ route('orders.refund-requests.store', $order) }}" class="mt-3 space-y-3">@csrf
                        <select name="reason" class="w-full rounded border border-amber-200 px-3 py-2 text-sm" required><option value="">Pilih alasan</option><option>Barang tidak sesuai</option><option>Barang rusak</option><option>Barang tidak sampai</option><option>Lainnya</option></select>
                        <textarea name="description" rows="3" class="w-full rounded border border-amber-200 px-3 py-2 text-sm" placeholder="Jelaskan masalah minimal 10 karakter" required></textarea>
                        <button class="w-full rounded bg-amber-600 px-4 py-2 font-medium text-white hover:bg-amber-700">Kirim pengajuan</button>
                    </form>
                </section>
            @endif
            @if ($order->refundRequest)
                <section class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-sm"><h2 class="font-bold text-amber-900">Pengajuan pengembalian</h2><p class="mt-2 font-medium">{{ $order->refundRequest->reason }} · {{ str_replace('_', ' ', $order->refundRequest->status->value) }}</p><p class="mt-1 whitespace-pre-line text-amber-900">{{ $order->refundRequest->description }}</p>@if($order->refundRequest->resolution_note)<p class="mt-2 text-amber-800">Catatan admin: {{ $order->refundRequest->resolution_note }}</p>@endif</section>
            @endif
            @if ($viewerIsBuyer && $order->status->value === 'completed')
            @if ($order->review)
            <section class="rounded-xl border bg-white p-5">
            <h2 class="font-bold">Ulasan Anda</h2>
            <p class="mt-2 text-yellow-500">{{ str_repeat('★', $order->review->rating) }}{{ str_repeat('☆', 5 - $order->review->rating) }}</p>
            <p class="mt-1 text-sm text-gray-700">{{ $order->review->comment ?: '-' }}</p>
          </section>
    @else
        <section class="rounded-xl border bg-white p-5">
            <h2 class="font-bold">Beri ulasan</h2>
            <form method="POST" action="{{ route('orders.review.store', $order) }}" class="mt-3 space-y-3">
                @csrf
                <div>
                    <label class="block text-sm">Rating</label>
                    <select name="rating" class="mt-1 w-full rounded border px-3 py-2" required>
                        <option value="">-- Pilih --</option>
                        @for ($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" @selected(old('rating') == $i)>{{ $i }} {{ str_repeat('★', $i) }}</option>
                        @endfor
                    </select>
                    @error('rating')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm">Komentar (opsional)</label>
                    <textarea name="comment" rows="3" class="mt-1 w-full rounded border px-3 py-2">{{ old('comment') }}</textarea>
                    @error('comment')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <button class="w-full rounded bg-blue-600 px-4 py-2 font-medium text-white">Kirim ulasan</button>
            </form>
        </section>
    @endif
@endif
            
        </aside>
    </div>
@endsection
