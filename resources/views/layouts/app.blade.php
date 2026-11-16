<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SECONDTECH')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f9fc] text-slate-800 antialiased">
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex h-20 max-w-7xl items-center gap-5 px-4 lg:px-6">
            <a href="{{ route('catalog.index') }}" class="flex shrink-0 items-center gap-2.5 text-base font-bold tracking-tight text-[#1257d6] sm:text-lg" aria-label="SECONDTECH beranda">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#1257d6] text-base text-white shadow-sm">⚡</span>
                <span>SECONDTECH</span>
            </a>

            <nav class="hidden items-center gap-6 text-sm font-medium text-slate-600 xl:flex">
                <a href="{{ route('catalog.index') }}" class="hover:text-[#1257d6]">Beranda</a>
                <a href="{{ route('products.index') }}" class="hover:text-[#1257d6]">Marketplace</a>
                <a href="{{ route('products.index') }}" class="hover:text-[#1257d6]">Kategori</a>
                @auth
                    @unless (auth()->user()->isAdmin())
                        <a href="{{ route('orders.index') }}" class="hover:text-[#1257d6]">Pesanan Saya</a>
                    @endunless
                @endauth
            </nav>

            <form method="GET" action="{{ route('products.index') }}" class="hidden min-w-0 flex-1 md:block">
                <label for="global-search" class="sr-only">Cari produk</label>
                <div class="relative mx-auto max-w-lg xl:max-w-xl">
                    <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-base text-slate-400">⌕</span>
                    <input id="global-search" name="q" value="{{ request('q') }}" placeholder="Cari laptop, iPhone, GPU bekas" class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-[#1257d6] focus:bg-white focus:ring-2 focus:ring-blue-100">
                </div>
            </form>

            <div class="ml-auto flex items-center gap-2 text-sm">
                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="rounded-xl px-4 py-2.5 font-semibold text-[#1257d6] hover:bg-blue-50">Admin</a>
                    @else
                        <a href="{{ route('seller.products.create') }}" class="hidden rounded-xl bg-[#1257d6] px-4 py-3 font-semibold text-white shadow-sm hover:bg-blue-700 sm:inline-flex">+ Jual Barang</a>
                        <a href="{{ route('chats.index') }}" class="rounded-xl p-3 text-base text-slate-500 hover:bg-slate-100 hover:text-[#1257d6]" aria-label="Pesan">◌</a>
                        <a href="{{ route('seller.dashboard') }}" class="hidden rounded-xl px-3 py-3 font-medium text-slate-600 hover:bg-slate-100 2xl:inline-flex">Dashboard</a>
                    @endif
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 rounded-xl p-2 hover:bg-slate-100">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 font-bold text-[#1257d6]">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="hidden max-w-28 truncate font-medium text-slate-700 2xl:block">{{ auth()->user()->name }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="hidden xl:block">
                        @csrf
                        <button type="submit" class="rounded-xl px-3 py-3 text-slate-500 hover:bg-red-50 hover:text-red-600">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-xl px-4 py-3 font-medium text-slate-600 hover:bg-slate-100">Masuk</a>
                    <a href="{{ route('register') }}" class="rounded-xl bg-[#1257d6] px-4 py-3 font-semibold text-white shadow-sm hover:bg-blue-700">Daftar</a>
                @endauth
            </div>
        </div>
        <div class="border-t border-slate-100 bg-white">
            <div class="mx-auto flex max-w-7xl items-center gap-6 overflow-x-auto px-4 py-3 text-xs font-medium text-slate-500 sm:text-sm lg:px-6">
                <a href="{{ route('products.index') }}" class="whitespace-nowrap hover:text-[#1257d6]">Smartphone & Tablet</a>
                <a href="{{ route('products.index') }}" class="whitespace-nowrap hover:text-[#1257d6]">Laptop & Komputer</a>
                <a href="{{ route('products.index') }}" class="whitespace-nowrap hover:text-[#1257d6]">Komponen & GPU</a>
                <a href="{{ route('products.index') }}" class="whitespace-nowrap hover:text-[#1257d6]">Audio & Kamera</a>
                <a href="{{ route('products.index') }}" class="whitespace-nowrap hover:text-[#1257d6]">Aksesoris</a>
                <span class="ml-auto hidden whitespace-nowrap text-emerald-600 lg:inline">✓ Transaksi aman & terlacak</span>
            </div>
        </div>
    </header>

    <div class="border-b border-blue-200 bg-[#1257d6] text-center text-sm font-medium text-white">
        <div class="mx-auto max-w-7xl px-4 py-2.5">Perhatian: seluruh transaksi tercatat di sistem. Gunakan chat dan resi untuk keamanan pembelian.</div>
    </div>

    <main class="mx-auto max-w-7xl px-4 py-6 lg:px-6 lg:py-8">
        @if (session('success'))
            <div class="mb-5 rounded border border-green-200 bg-green-50 p-3 text-green-800">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-5 rounded border border-red-200 bg-red-50 p-3 text-red-800">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>
