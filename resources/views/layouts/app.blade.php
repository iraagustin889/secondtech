<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SECONDTECH')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-800">
    <nav class="bg-white border-b">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/" class="text-xl font-bold text-blue-600">SECONDTECH</a>

            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('products.index') }}" class="hover:underline">Katalog</a>
                @auth
                    {{-- Tautan kelola produk milik user --}}
                    <a href="{{ route('seller.products.index') }}" class="hover:underline">
                        Produk Saya
                    </a>
                    <a href="{{ route('seller.orders.index') }}" class="hover:underline">Penjualan</a>

                    <a href="{{ route('profile.edit') }}" class="hover:underline">Halo, {{ auth()->user()->name }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-red-600 hover:underline">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="hover:underline">Login</a>
                    <a href="{{ route('register') }}" class="bg-blue-600 text-white px-3 py-1.5 rounded hover:bg-blue-700">Daftar</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto px-4 py-8">
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
