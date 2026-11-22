@extends('layouts.app')

@section('title', 'Checkout '.$product->name)

@section('content')
    <div class="mx-auto max-w-6xl">
        <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
            <div>
                <a href="{{ route('products.show', $product) }}" class="text-xs font-semibold text-[#1257d6] hover:underline">← Kembali ke produk</a>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Checkout aman</h1>
                <p class="mt-1 text-sm text-slate-500">Pilih bayar tunai saat bertemu atau QRIS Universal dengan rekber.</p>
            </div>
        </div>

        <ol class="mb-6 grid grid-cols-3 gap-2 rounded-xl border border-slate-200 bg-white p-3 text-center text-[11px] font-medium text-slate-400 shadow-sm sm:text-xs">
            <li class="text-[#1257d6]"><span class="mr-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-[#1257d6] text-[10px] text-white">1</span> Pilih pembayaran</li>
            <li><span class="mr-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-[10px]">2</span> Atur pertemuan</li>
            <li><span class="mr-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-[10px]">3</span> Transaksi selesai</li>
        </ol>

        @if ($errors->any())
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif

        <form id="checkout-form" method="POST" action="{{ route('checkout.store', $product) }}" class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
            @csrf
            <div class="space-y-5">
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div><h2 class="font-bold text-slate-900">Data pembeli</h2><p class="mt-1 text-xs text-slate-500">Kontak ini dipakai penjual untuk menyepakati lokasi dan waktu serah-terima.</p></div>
                        <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-[#1257d6]">Transaksi langsung</span>
                    </div>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div><label class="mb-1.5 block text-xs font-semibold text-slate-700">Nama</label><input name="recipient_name" value="{{ $recipient['recipient_name'] }}" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-[#1257d6] focus:ring-2 focus:ring-blue-100" required></div>
                        <div><label class="mb-1.5 block text-xs font-semibold text-slate-700">Nomor WhatsApp/telepon</label><input name="recipient_phone" value="{{ $recipient['recipient_phone'] }}" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-[#1257d6] focus:ring-2 focus:ring-blue-100" required></div>
                    </div>
                    <div class="mt-4"><label class="mb-1.5 block text-xs font-semibold text-slate-700">Alamat atau area domisili</label><textarea name="recipient_address" rows="3" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-[#1257d6] focus:ring-2 focus:ring-blue-100" placeholder="Contoh: Kecamatan, patokan area" required>{{ $recipient['recipient_address'] }}</textarea></div>
                    <div class="mt-4"><label class="mb-1.5 block text-xs font-semibold text-slate-700">Kota/lokasi</label><input name="recipient_location" value="{{ $recipient['recipient_location'] }}" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-[#1257d6] focus:ring-2 focus:ring-blue-100" placeholder="Contoh: Banyuwangi" required></div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-bold text-slate-900">Produk yang dibeli</h2>
                    <div class="mt-4 flex gap-4 rounded-xl bg-slate-50 p-3">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-3xl">{{ $product->category?->name === 'Laptop' ? '💻' : '📦' }}</div>
                        <div class="min-w-0 flex-1"><p class="font-semibold text-slate-800">{{ $product->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $product->condition }} · Penjual di {{ $product->location }}</p><p class="mt-2 font-bold text-[#1257d6]">Rp {{ number_format($product->price, 0, ',', '.') }}</p></div>
                    </div>
                    <div class="mt-4"><label for="quantity" class="mb-1.5 block text-xs font-semibold text-slate-700">Jumlah pesanan</label><div class="flex items-center gap-3"><input id="quantity" type="number" name="quantity" min="1" max="{{ $product->stock }}" value="{{ old('quantity', $quantity) }}" class="w-24 rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-[#1257d6]" required><span class="text-xs text-slate-500">Maksimal {{ $product->stock }} unit.</span></div></div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div><h2 class="font-bold text-slate-900">Metode pembayaran</h2><p class="mt-1 text-xs text-slate-500">Hanya dua opsi agar transaksi mudah dan jelas.</p></div>
                    @error('payment_method')<p class="mt-3 text-sm text-red-600">{{ $message }}</p>@enderror
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer gap-3 rounded-xl border border-slate-200 p-4 transition has-[:checked]:border-[#1257d6] has-[:checked]:bg-blue-50">
                            <input type="radio" name="payment_method" value="cod" class="mt-1" @checked(old('payment_method') === 'cod')>
                            <span><strong class="text-sm text-slate-800">COD / Bayar saat bertemu</strong><span class="mt-1 block text-xs leading-5 text-slate-500">Bayar tunai langsung ke penjual saat barang diperiksa. Tanpa biaya layanan marketplace.</span></span>
                        </label>
                        <label class="flex cursor-pointer gap-3 rounded-xl border border-slate-200 p-4 transition has-[:checked]:border-[#1257d6] has-[:checked]:bg-blue-50">
                            <input type="radio" name="payment_method" value="qris" class="mt-1" @checked(old('payment_method', 'qris') === 'qris')>
                            <span><strong class="text-sm text-slate-800">QRIS Universal · Rekber</strong><span class="mt-1 block text-xs leading-5 text-slate-500">Bayar dengan satu QRIS. Dana ditahan sampai kedua pihak mengonfirmasi serah-terima.</span></span>
                        </label>
                    </div>
                    <div id="payment-note" class="mt-4 rounded-lg border border-blue-100 bg-blue-50 p-3 text-xs leading-5 text-blue-900">Dengan QRIS, jadwal pertemuan diatur penjual setelah pembayaran berhasil. Bila ada masalah sebelum serah-terima, pembeli dapat mengajukan pengembalian dana.</div>
                </section>
            </div>

            <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:sticky lg:top-28">
                <h2 class="font-bold text-slate-900">Ringkasan transaksi</h2>
                <dl class="mt-5 space-y-3 text-sm">
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Subtotal produk</dt><dd id="summary-subtotal" class="font-medium text-slate-800">Rp {{ number_format((int) $product->price * $quantity, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Ongkir</dt><dd class="font-medium text-slate-800">Rp 0</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-slate-500">Biaya layanan <span id="service-fee-label" class="text-[11px]">{{ $serviceFeeLabel ? '('.$serviceFeeLabel.')' : '' }}</span></dt><dd id="summary-service-fee" class="font-medium text-slate-800">Rp {{ number_format($qrisServiceFee, 0, ',', '.') }}</dd></div>
                    <div class="border-t border-slate-100 pt-4"><div class="flex justify-between gap-4"><dt class="font-bold text-slate-800">Total pembayaran</dt><dd id="summary-total" class="text-lg font-bold text-[#1257d6]">Rp {{ number_format((int) $product->price * $quantity + $qrisServiceFee, 0, ',', '.') }}</dd></div><p id="summary-caption" class="mt-1 text-[11px] text-slate-500">Dana QRIS disimpan aman di rekber hingga transaksi selesai.</p></div>
                </dl>
                <button class="mt-6 w-full rounded-lg bg-[#1257d6] px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Buat pesanan</button>
                <a href="{{ route('products.show', $product) }}" class="mt-3 block text-center text-xs font-medium text-slate-500 hover:text-[#1257d6]">Batalkan</a>
                <div class="mt-5 rounded-lg bg-slate-50 p-3 text-[11px] leading-5 text-slate-500">🤝 Setelah pesanan dibuat, gunakan chat untuk menyepakati tempat dan waktu serah-terima yang aman.</div>
            </aside>
        </form>
    </div>

    <script>
        const productPrice = {{ (int) $product->price }};
        const initialQuantity = {{ $quantity }};
        const qrisServiceFee = {{ $qrisServiceFee }};
        const serviceFeeType = @json($serviceFeeType);
        const serviceFeeValue = {{ $serviceFeeValue }};
        const quantity = document.getElementById('quantity');
        const summarySubtotal = document.getElementById('summary-subtotal');
        const summaryServiceFee = document.getElementById('summary-service-fee');
        const summaryTotal = document.getElementById('summary-total');
        const summaryCaption = document.getElementById('summary-caption');
        const paymentNote = document.getElementById('payment-note');
        const rupiah = (amount) => `Rp ${new Intl.NumberFormat('id-ID').format(amount)}`;
        const currentQuantity = () => Math.max(1, Math.min(Number(quantity.value || initialQuantity), {{ $product->stock }}));
        const calculateQrisFee = (subtotal) => serviceFeeType === 'percentage'
            ? Math.round(subtotal * serviceFeeValue / 100)
            : (serviceFeeType === 'fixed' ? Math.round(serviceFeeValue) : qrisServiceFee);
        const updateSummary = () => {
            const subtotal = productPrice * currentQuantity();
            const isCod = document.querySelector('input[name="payment_method"]:checked')?.value === 'cod';
            const fee = isCod ? 0 : calculateQrisFee(subtotal);
            summarySubtotal.textContent = rupiah(subtotal);
            summaryServiceFee.textContent = rupiah(fee);
            summaryTotal.textContent = rupiah(subtotal + fee);
            summaryCaption.textContent = isCod
                ? 'Bayar tunai langsung saat bertemu dengan penjual. Tidak ada biaya layanan marketplace.'
                : 'Dana QRIS disimpan aman di rekber hingga kedua pihak mengonfirmasi serah-terima.';
            paymentNote.textContent = isCod
                ? 'Setelah pesanan dibuat, penjual mengatur lokasi dan waktu serah-terima. Bawa jumlah uang tunai sesuai total.'
                : 'Setelah QRIS berhasil, penjual mengatur jadwal serah-terima. Jika ada masalah sebelum barang diterima, ajukan pengembalian dana.';
        };
        quantity.addEventListener('input', updateSummary);
        document.querySelectorAll('input[name="payment_method"]').forEach((input) => input.addEventListener('change', updateSummary));
        updateSummary();
    </script>
@endsection
