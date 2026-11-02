@extends('layouts.app')

@section('title', 'Checkout '.$product->name)

@section('content')
    <div class="mx-auto grid max-w-5xl gap-6 lg:grid-cols-3">
        <aside class="rounded-xl border bg-white p-5 lg:col-span-1">
            <p class="text-sm text-gray-500">Produk yang dibeli</p>
            <h1 class="mt-1 text-xl font-bold">{{ $product->name }}</h1>
            <p class="mt-2 text-lg font-bold text-blue-700">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
            <p class="mt-3 text-sm text-gray-600">Berat {{ number_format($product->weight) }} gram · {{ $product->location }}</p>
        </aside>

        <section class="rounded-xl border bg-white p-6 lg:col-span-2">
            <h2 class="text-2xl font-bold">Alamat & pengiriman</h2>
            <p class="mt-1 text-sm text-gray-600">Untuk ekspedisi, cari dan pilih lokasi tujuan terlebih dahulu. COD tidak menghitung ongkir.</p>

            @if ($errors->any())
                <div class="mt-4 rounded bg-red-50 p-3 text-red-800">
                    @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <form id="checkout-form" method="POST" action="{{ route('checkout.store', $product) }}" class="mt-6 space-y-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="mb-1 block text-sm font-medium">Nama penerima</label><input name="recipient_name" value="{{ $recipient['recipient_name'] }}" class="w-full rounded border px-3 py-2" required></div>
                    <div><label class="mb-1 block text-sm font-medium">Telepon penerima</label><input name="recipient_phone" value="{{ $recipient['recipient_phone'] }}" class="w-full rounded border px-3 py-2" required></div>
                </div>
                <div><label class="mb-1 block text-sm font-medium">Alamat lengkap</label><textarea name="recipient_address" rows="3" class="w-full rounded border px-3 py-2" required>{{ $recipient['recipient_address'] }}</textarea></div>
                <div><label class="mb-1 block text-sm font-medium">Kota/lokasi penerima</label><input name="recipient_location" value="{{ $recipient['recipient_location'] }}" class="w-full rounded border px-3 py-2" required></div>

                <div class="rounded border bg-gray-50 p-4">
                    <label for="destination-search" class="mb-1 block text-sm font-medium">Cari lokasi tujuan ekspedisi</label>
                    <div class="flex gap-2"><input id="destination-search" class="min-w-0 flex-1 rounded border px-3 py-2" placeholder="Minimal 3 huruf, contoh: Bandung"><button id="destination-search-button" type="button" class="rounded border px-3 py-2">Cari</button></div>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <div><label class="mb-1 block text-xs font-medium">Pilih lokasi</label><select id="destination-id" name="destination_id" class="w-full rounded border px-3 py-2"><option value="">Cari lokasi terlebih dahulu</option></select></div>
                        <input id="destination-name" name="destination_name" type="hidden" value="{{ old('destination_name', $checkout['destination_name'] ?? '') }}">
                        <div><label class="mb-1 block text-xs font-medium">ID tujuan</label><input id="destination-id-manual" type="number" class="w-full rounded border bg-gray-100 px-3 py-2" placeholder="Terisi saat memilih lokasi" readonly></div>
                    </div>
                    <p id="destination-message" class="mt-2 text-xs text-gray-500">ID tujuan akan terisi otomatis setelah lokasi dipilih.</p>
                </div>

                <div class="space-y-3">
                    <p class="font-medium">Metode pengiriman</p>
                    <label class="flex items-center gap-2 rounded border p-3"><input type="radio" name="shipping_method" value="cod" @checked(old('shipping_method') === 'cod')> <span><strong>COD</strong><br><span class="text-sm text-gray-600">Penjual akan menyetujui lokasi dan waktu serah-terima.</span></span></label>
                    <label class="flex items-center gap-2 rounded border p-3"><input type="radio" name="shipping_method" value="expedition" @checked(old('shipping_method', ! empty($checkout['quotes']) ? 'expedition' : '') === 'expedition')> <span><strong>Ekspedisi</strong><br><span class="text-sm text-gray-600">Pilih layanan setelah mengecek ongkir.</span></span></label>
                </div>

                @if (! empty($checkout['quotes']))
                    <div class="space-y-2 rounded border border-blue-200 bg-blue-50 p-4">
                        <p class="font-medium">Pilih layanan ekspedisi</p>
                        @foreach ($checkout['quotes'] as $quote)
                            <label class="flex cursor-pointer items-center justify-between gap-3 rounded border bg-white p-3">
                                <span><input type="radio" name="quote_key" value="{{ $quote['key'] }}" @checked(old('quote_key') === $quote['key'])> <strong>{{ strtoupper($quote['courier']) }} {{ $quote['service'] }}</strong><span class="block text-sm text-gray-600">Estimasi {{ $quote['estimate'] }}</span></span>
                                <strong>Rp {{ number_format($quote['cost'], 0, ',', '.') }}</strong>
                            </label>
                        @endforeach
                    </div>
                @endif

                <div class="flex flex-wrap gap-3 pt-2">
                    <button formaction="{{ route('checkout.quote', $product) }}" class="rounded border border-blue-600 px-4 py-2 font-medium text-blue-700">Cek ongkir</button>
                    <button class="rounded bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700">Buat pesanan</button>
                    <a href="{{ route('products.show', $product) }}" class="px-4 py-2 text-gray-600 underline">Batal</a>
                </div>
            </form>
        </section>
    </div>

    <script>
        const searchInput = document.getElementById('destination-search');
        const searchButton = document.getElementById('destination-search-button');
        const destinationSelect = document.getElementById('destination-id');
        const destinationName = document.getElementById('destination-name');
        const manualId = document.getElementById('destination-id-manual');
        const message = document.getElementById('destination-message');
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
        });
    </script>
@endsection
