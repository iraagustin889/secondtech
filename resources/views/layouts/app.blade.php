<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SECONDTECH')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#f7f9fc] text-slate-800 antialiased">
    @php
        $navActive = 'border-b-2 border-[#1257d6] pb-1 font-bold text-[#1257d6]';
        $navIdle = 'border-b-2 border-transparent pb-1 hover:text-[#1257d6]';
        $unreadChatCount = auth()->check()
            ? \App\Models\Message::query()
                ->whereNull('read_at')
                ->where('sender_id', '!=', auth()->id())
                ->whereHas('conversation', fn ($query) => $query
                    ->where('buyer_id', auth()->id())
                    ->orWhere('seller_id', auth()->id()))
                ->count()
            : 0;
    @endphp
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
        <div class="mx-auto flex h-20 max-w-7xl items-center gap-5 px-4 lg:px-6">
            <a href="{{ route('catalog.index') }}" class="flex shrink-0 items-center gap-2.5 text-base font-bold tracking-tight text-[#1257d6] sm:text-lg" aria-label="SECONDTECH">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#1257d6] text-base text-white shadow-sm">⚡</span>
                <span>SECONDTECH</span>
            </a>

            <nav class="hidden items-center gap-5 text-sm font-medium text-slate-600 lg:flex">
                @auth
                    @unless (auth()->user()->isAdmin())
                        <a href="{{ route('catalog.index') }}" class="{{ request()->routeIs('catalog.index', 'products.*') ? $navActive : $navIdle }}">Beranda</a>
                        <a href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.*') ? $navActive : $navIdle }}">Pesanan Saya</a>
                        <a href="{{ route('seller.products.index') }}" class="{{ request()->routeIs('seller.products.*') && ! request()->routeIs('seller.products.create') ? $navActive : $navIdle }}">Produk Saya</a>
                        <a href="{{ route('seller.orders.index') }}" class="{{ request()->routeIs('seller.orders.*') ? $navActive : $navIdle }}">Penjualan Saya</a>
                        <a href="{{ route('chats.index') }}" class="{{ request()->routeIs('chats.*') ? $navActive : $navIdle }}">
                            Pesan
                            @if ($unreadChatCount)
                                <span class="ml-1 inline-flex min-w-5 items-center justify-center rounded-full bg-red-600 px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $unreadChatCount }}</span>
                            @endif
                        </a>
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
                        <a href="{{ route('seller.notifications.index') }}" class="relative hidden rounded-xl px-3 py-3 font-medium hover:bg-slate-100 2xl:inline-flex {{ request()->routeIs('seller.notifications.*') ? 'bg-blue-50 font-bold text-[#1257d6]' : 'text-slate-600' }}">
                            Notifikasi
                            @if (auth()->user()->unreadNotifications()->count() > 0)
                                <span class="ml-1 inline-flex min-w-5 items-center justify-center rounded-full bg-red-600 px-1.5 py-0.5 text-xs font-bold text-white">{{ auth()->user()->unreadNotifications()->count() }}</span>
                            @endif
                        </a>
                    @endif
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 rounded-xl p-2 hover:bg-slate-100 {{ request()->routeIs('profile.*') ? 'bg-blue-50' : '' }}">
                        @if (auth()->user()->avatarUrl())
                            <img src="{{ auth()->user()->avatarUrl() }}" alt="Foto profil" class="h-9 w-9 rounded-full border border-slate-200 object-cover">
                        @else
                            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-100 font-bold text-[#1257d6]">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name, 0, 1)) }}</span>
                        @endif
                        <span class="hidden max-w-28 truncate font-medium text-slate-700 2xl:block">{{ auth()->user()->name }}</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="hidden 2xl:block">
                        @csrf
                        <button type="submit" class="rounded-xl px-3 py-3 text-slate-500 hover:bg-red-50 hover:text-red-600">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-xl px-4 py-3 font-medium text-slate-600 hover:bg-slate-100">Masuk</a>
                    <a href="{{ route('register') }}" class="rounded-xl bg-[#1257d6] px-4 py-3 font-semibold text-white shadow-sm hover:bg-blue-700">Daftar</a>
                @endauth
            </div>
        </div>
    </header>

    @auth
        @unless (auth()->user()->isAdmin())
            <nav class="overflow-x-auto border-b border-slate-200 bg-white lg:hidden" aria-label="Navigasi utama">
                <div class="mx-auto flex min-w-max max-w-7xl gap-5 px-4 py-3 text-sm font-semibold text-slate-600">
                    <a href="{{ route('catalog.index') }}" class="{{ request()->routeIs('catalog.index', 'products.*') ? 'text-[#1257d6]' : '' }}">Beranda</a>
                    <a href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.*') ? 'text-[#1257d6]' : '' }}">Pesanan Saya</a>
                    <a href="{{ route('seller.products.index') }}" class="{{ request()->routeIs('seller.products.*') ? 'text-[#1257d6]' : '' }}">Produk Saya</a>
                    <a href="{{ route('seller.orders.index') }}" class="{{ request()->routeIs('seller.orders.*') ? 'text-[#1257d6]' : '' }}">Penjualan Saya</a>
                    <a href="{{ route('chats.index') }}" class="{{ request()->routeIs('chats.*') ? 'text-[#1257d6]' : '' }}">
                        Pesan
                        @if ($unreadChatCount) ({{ $unreadChatCount }}) @endif
                    </a>
                </div>
            </nav>
        @endunless
    @endauth

    <div class="border-b border-blue-200 bg-[#1257d6] text-center text-sm font-medium text-white">
        <div class="mx-auto max-w-7xl px-4 py-2.5">Perhatian: seluruh transaksi tercatat di sistem. Gunakan chat dan pilih lokasi pertemuan yang aman.</div>
    </div>

    @auth
        @unless (auth()->user()->isAdmin())
            <a href="{{ route('chats.index') }}" class="fixed bottom-5 right-5 z-50 inline-flex h-14 w-14 items-center justify-center rounded-full bg-[#1257d6] text-white shadow-lg shadow-blue-900/25 transition hover:-translate-y-0.5 hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200" aria-label="Buka pesan">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.9 9.9 0 0 1-4.255-.949L3 20l1.207-3.622A7.33 7.33 0 0 1 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z" />
                </svg>
                @if ($unreadChatCount)
                    <span class="absolute -right-1 -top-1 inline-flex min-w-5 items-center justify-center rounded-full border-2 border-white bg-red-600 px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $unreadChatCount }}</span>
                @endif
                <span class="sr-only">Pesan</span>
            </a>
        @endunless
    @endauth

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
