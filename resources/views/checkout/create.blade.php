@extends('layouts.app')

@section('title', 'Checkout '.$product->name)

@section('content')
    <div class="mx-auto max-w-6xl">
        <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
            <div>
                <a href="{{ route('products.show', $product) }}" class="text-xs font-semibold text-[#1257d6] hover:underline">← Kembali ke produk</a>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900">Pengiriman & Checkout</h1>
                <p class="mt-1 text-sm text-slate-500">Lengkapi alamat, pilih ekspedisi, lalu lanjutkan pembayaran dengan aman.</p>
            </div>
        </div>

        <ol class="mb-6 grid grid-cols-4 gap-2 rounded-xl border border-slate-200 bg-white p-3 text-center text-[11px] font-medium text-slate-400 shadow-sm sm:text-xs">
            <li><span class="mr-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-[10px]">1</span> Keranjang</li>
            <li class="text-[#1257d6]"><span class="mr-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-[#1257d6] text-[10px] text-white">2</span> Pengiriman</li>
            <li><span class="mr-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-[10px]">3</span> Pembayaran</li>
            <li><span class="mr-1 inline-flex h-5 w-5 items-center justify-center rounded-full bg-slate-100 text-[10px]">4</span> Selesai</li>
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
                    <div class="flex items-start justify-between gap-3"><div><h2 class="font-bold text-slate-900">Alamat penerima</h2><p class="mt-1 text-xs text-slate-500">Digunakan untuk perhitungan ongkir dan pengiriman paket.</p></div><span class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-[#1257d6]">Alamat utama</span></div>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2">
                        <div><label class="mb-1.5 block text-xs font-semibold text-slate-700">Nama penerima</label><input name="recipient_name" value="{{ $recipient['recipient_name'] }}" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-[#1257d6] focus:ring-2 focus:ring-blue-100" required></div>
                        <div><label class="mb-1.5 block text-xs font-semibold text-slate-700">Telepon penerima</label><input name="recipient_phone" value="{{ $recipient['recipient_phone'] }}" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-[#1257d6] focus:ring-2 focus:ring-blue-100" required></div>
                    </div>
                    <div class="mt-4"><label class="mb-1.5 block text-xs font-semibold text-slate-700">Alamat lengkap</label><textarea name="recipient_address" rows="3" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-[#1257d6] focus:ring-2 focus:ring-blue-100" required>{{ $recipient['recipient_address'] }}</textarea></div>
                    <div class="mt-4"><label class="mb-1.5 block text-xs font-semibold text-slate-700">Kota/lokasi penerima</label><input name="recipient_location" value="{{ $recipient['recipient_location'] }}" class="w-full rounded-lg border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-[#1257d6] focus:ring-2 focus:ring-blue-100" placeholder="Contoh: Banyuwangi" required></div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-bold text-slate-900">Produk yang dibeli</h2>
                    <div class="mt-4 flex gap-4 rounded-xl bg-slate-50 p-3">
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-3xl">{{ $product->category?->name === 'Laptop' ? '💻' : '📦' }}</div>
                        <div class="min-w-0 flex-1"><p class="font-semibold text-slate-800">{{ $product->name }}</p><p class="mt-1 text-xs text-slate-500">{{ $product->condition }} · {{ number_format($product->weight) }} gram · {{ $product->location }}</p><p class="mt-2 font-bold text-[#1257d6]">Rp {{ number_format($product->price, 0, ',', '.') }}</p></div>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div><h2 class="font-bold text-slate-900">Pilih pengiriman</h2><p class="mt-1 text-xs text-slate-500">Cari lokasi tujuan untuk menampilkan tarif ekspedisi resmi secara otomatis.</p></div>
                    <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <label for="destination-search" class="mb-2 block text-xs font-semibold text-slate-700">Cari lokasi tujuan ekspedisi</label>
                        <div class="flex gap-2"><input id="destination-search" class="min-w-0 flex-1 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-[#1257d6]" placeholder="Minimal 3 huruf, contoh: Bandung"><button id="destination-search-button" type="button" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:border-[#1257d6] hover:text-[#1257d6]">Cari</button></div>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div><label class="mb-1.5 block text-xs font-semibold text-slate-600">Pilih lokasi</label><select id="destination-id" name="destination_id" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-[#1257d6]"><option value="">Cari lokasi terlebih dahulu</option></select></div>
                            <input id="destination-name" name="destination_name" type="hidden" value="{{ old('destination_name', $checkout['destination_name'] ?? '') }}">
                            <div><label class="mb-1.5 block text-xs font-semibold text-slate-600">ID tujuan</label><input id="destination-id-manual" type="number" class="w-full rounded-lg border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm text-slate-400" placeholder="Terisi otomatis" readonly></div>
                        </div>
                        <p id="destination-message" class="mt-3 text-xs text-slate-500">ID tujuan akan terisi otomatis setelah lokasi dipilih.</p>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer gap-3 rounded-xl border border-slate-200 p-4 transition has-[:checked]:border-[#1257d6] has-[:checked]:bg-blue-50"><input type="radio" name="shipping_method" value="cod" class="mt-1" @checked(old('shipping_method') === 'cod')><span><strong class="text-sm text-slate-800">COD</strong><span class="mt-1 block text-xs leading-5 text-slate-500">Atur lokasi dan waktu serah-terima bersama penjual.</span></span></label>
                        <label class="flex cursor-pointer gap-3 rounded-xl border border-slate-200 p-4 transition has-[:checked]:border-[#1257d6] has-[:checked]:bg-blue-50"><input type="radio" name="shipping_method" value="expedition" class="mt-1" @checked(old('shipping_method', ! empty($checkout['quotes']) ? 'expedition' : '') === 'expedition')><span><strong class="text-sm text-slate-800">Ekspedisi</strong><span class="mt-1 block text-xs leading-5 text-slate-500">Pilih layanan dengan tarif dari RajaOngkir.</span></span></label>
                    </div>

                    @if (! empty($checkout['quotes']))
                        <div class="mt-4 space-y-2 rounded-xl border border-blue-100 bg-blue-50/50 p-4">
                            <p class="text-sm font-bold text-slate-800">Pilih layanan ekspedisi</p>
                            @foreach ($checkout['quotes'] as $quote)
                                <label class="flex cursor-pointer items-center justify-between gap-3 rounded-lg border border-slate-200 bg-white p-3 transition has-[:checked]:border-[#1257d6]">
                                    <span class="flex items-center gap-3"><input type="radio" name="quote_key" value="{{ $quote['key'] }}" data-cost="{{ $quote['cost'] }}" @checked(old('quote_key') === $quote['key'])><span><strong class="text-sm text-slate-800">{{ strtoupper($quote['courier']) }} {{ $quote['service'] }}</strong><span class="block text-xs text-slate-500">Estimasi {{ $quote['estimate'] }}</span></span></span>
                                    <strong class="text-sm text-[#1257d6]">Rp {{ number_format($quote['cost'], 0, ',', '.') }}</strong>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section id="payment-method-section" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div><h2 class="font-bold text-slate-900">Metode pembayaran</h2><p class="mt-1 text-xs text-slate-500">Pilih metode untuk gerbang pembayaran lokal. Dana ditahan oleh rekber sampai transaksi selesai.</p></div>
                    @error('payment_method')<p class="mt-3 text-sm text-red-600">{{ $message }}</p>@enderror
                    <div class="mt-4 grid gap-3 sm:grid-cols-3">
                        <label class="flex cursor-pointer gap-3 rounded-xl border border-slate-200 p-4 transition has-[:checked]:border-[#1257d6] has-[:checked]:bg-blue-50"><input type="radio" name="payment_method" value="bank_transfer" class="mt-1" @checked(old('payment_method', 'bank_transfer') === 'bank_transfer')><span><strong class="text-sm text-slate-800">Virtual Account</strong><span class="mt-1 block text-xs text-slate-500">Pembayaran bank otomatis</span></span></label>
                        <label class="flex cursor-pointer gap-3 rounded-xl border border-slate-200 p-4 transition has-[:checked]:border-[#1257d6] has-[:checked]:bg-blue-50"><input type="radio" name="payment_method" value="qris" class="mt-1" @checked(old('payment_method') === 'qris')><span><strong class="text-sm text-slate-800">QRIS</strong><span class="mt-1 block text-xs text-slate-500">Kode QR untuk demo lokal</span></span></label>
                        <label class="flex cursor-pointer gap-3 rounded-xl border border-slate-200 p-4 transition has-[:checked]:border-[#1257d6] has-[:checked]:bg-blue-50"><input type="radio" name="payment_method" value="e_wallet" class="mt-1" @checked(old('payment_method') === 'e_wallet')><span><strong class="text-sm text-slate-800">E-Wallet</strong><span class="mt-1 block text-xs text-slate-500">Saldo digital untuk demo lokal</span></span></label>
                    </div>
                </section>
            </div>

            <aside class="h-fit rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:sticky lg:top-28">
                <h2 class="font-bold text-slate-900">Ringkasan Transaksi</h2>
                <dl class="mt-5 space-y-3 text-sm"><div class="flex justify-between gap-4"><dt class="text-slate-500">Subtotal produk</dt><dd class="font-medium text-slate-800">Rp {{ number_format($product->price, 0, ',', '.') }}</dd></div><div class="flex justify-between gap-4"><dt class="text-slate-500">Ongkos kirim</dt><dd id="summary-shipping-cost" class="text-right text-xs text-slate-500">Pilih layanan</dd></div><div class="flex justify-between gap-4"><dt class="text-slate-500">Biaya layanan @if($serviceFeeLabel)<span class="text-[11px]">({{ $serviceFeeLabel }})</span>@endif</dt><dd class="font-medium text-slate-800">Rp {{ number_format($serviceFee, 0, ',', '.') }}</dd></div><div class="border-t border-slate-100 pt-4"><div class="flex justify-between gap-4"><dt class="font-bold text-slate-800">Total sementara</dt><dd id="summary-total" class="text-lg font-bold text-[#1257d6]">Rp {{ number_format((int) $product->price + $serviceFee, 0, ',', '.') }}</dd></div><p class="mt-1 text-[11px] text-slate-500">Total akhir diperbarui sesuai ongkir yang dipilih.</p></div></dl>
                <button formaction="{{ route('checkout.quote', $product) }}" class="mt-6 w-full rounded-lg border border-[#1257d6] px-4 py-2.5 text-sm font-semibold text-[#1257d6] hover:bg-blue-50">Cek ongkir resmi</button>
                <button class="mt-3 w-full rounded-lg bg-[#1257d6] px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-700">Lanjutkan pembayaran</button>
                <a href="{{ route('products.show', $product) }}" class="mt-3 block text-center text-xs font-medium text-slate-500 hover:text-[#1257d6]">Batalkan pesanan</a>
                <div class="mt-5 rounded-lg bg-slate-50 p-3 text-[11px] leading-5 text-slate-500">🔒 Data penerima dan pilihan pengiriman tercatat aman di sistem marketplace.</div>
            </aside>
        </form>
    </div>

    <script>
        const searchInput = document.getElementById('destination-search');
        const searchButton = document.getElementById('destination-search-button');
        const destinationSelect = document.getElementById('destination-id');
        const destinationName = document.getElementById('destination-name');
        const manualId = document.getElementById('destination-id-manual');
        const message = document.getElementById('destination-message');
        const checkoutForm = document.getElementById('checkout-form');
        const quoteUrl = @json(route('checkout.quote', $product));
        const shippingCost = document.getElementById('summary-shipping-cost');
        const total = document.getElementById('summary-total');
        const productPrice = {{ (int) $product->price }};
        const serviceFee = {{ $serviceFee }};
        const paymentSection = document.getElementById('payment-method-section');
        const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
        const rupiah = (amount) => `Rp ${new Intl.NumberFormat('id-ID').format(amount)}`;
        const updateTotal = (cost, label = null) => {
            shippingCost.textContent = label || rupiah(cost);
            total.textContent = rupiah(productPrice + serviceFee + cost);
        };
        const updateCheckoutMode = () => {
            const method = document.querySelector('input[name="shipping_method"]:checked')?.value;
            const isCod = method === 'cod';
            paymentSection.classList.toggle('hidden', isCod);
            paymentMethods.forEach((input) => { input.disabled = isCod; });
            if (isCod) updateTotal(0, 'Rp 0');
        };
        const findDestinations = async () => {
            const q = searchInput.value.trim();
            if (q.length < 3) { message.textContent = 'Masukkan minimal 3 huruf.'; return; }
            message.textContent = 'Mencari lokasi…';
            try {
                const response = await fetch(`{{ route('shipping.destinations') }}?q=${encodeURIComponent(q)}`, { headers: { Accept: 'application/json' } });
                const payload = await response.json();
                if (!response.ok) throw new Error(payload.message || 'Lokasi tidak ditemukan.');
                destinationSelect.innerHTML = '<option value="">Pilih lokasi tujuan</option>';
                payload.data.forEach((destination) => {
                    const option = new Option(destination.name, destination.id);
                    option.dataset.name = destination.name;
                    destinationSelect.add(option);
                });
                message.textContent = payload.data.length ? 'Pilih salah satu lokasi.' : 'Tidak ada lokasi yang ditemukan.';
            } catch (error) { message.textContent = error.message; }
        };
        searchButton.addEventListener('click', findDestinations);
        destinationSelect.addEventListener('change', () => {
            const option = destinationSelect.selectedOptions[0];
            destinationName.value = option?.dataset.name || '';
            manualId.value = destinationSelect.value;

            const recipientFields = ['recipient_name', 'recipient_phone', 'recipient_address', 'recipient_location'];
            const recipientIsComplete = recipientFields.every((field) => checkoutForm.elements[field].value.trim() !== '');

            if (destinationSelect.value && recipientIsComplete) {
                message.textContent = 'Mengecek ongkir otomatis…';
                checkoutForm.action = quoteUrl;
                checkoutForm.submit();
            } else if (destinationSelect.value) {
                message.textContent = 'Lengkapi data penerima, lalu tekan “Cek ulang ongkir”.';
            }
        });
        document.querySelectorAll('input[name="quote_key"]').forEach((input) => {
            input.addEventListener('change', () => updateTotal(Number(input.dataset.cost), rupiah(Number(input.dataset.cost))));
            if (input.checked) updateTotal(Number(input.dataset.cost), rupiah(Number(input.dataset.cost)));
        });
        document.querySelectorAll('input[name="shipping_method"]').forEach((input) => {
            input.addEventListener('change', () => {
                updateCheckoutMode();
            });
        });
        updateCheckoutMode();
    </script>
@endsection
