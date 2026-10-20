<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') - SECONDTECH</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 text-gray-800">
    <div class="flex min-h-screen">

        {{-- Sidebar --}}
        <aside class="w-56 bg-white border-r flex flex-col">
            <div class="px-4 py-4 border-b">
                <a href="{{ route('admin.dashboard') }}" class="text-lg font-bold text-blue-600">SECONDTECH</a>
                <p class="text-xs text-gray-500">Panel Admin</p>
            </div>

            <nav class="flex-1 p-3 space-y-1 text-sm">
                <a href="{{ route('admin.dashboard') }}"
                   class="block rounded px-3 py-2 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-100' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.users.index') }}"
                  class="block rounded px-3 py-2 {{ request()->routeIs('admin.users.*') ? 'bg-blue-50 text-blue-700 font-semibold' : 'hover:bg-gray-100' }}">
                  Pengguna
                </a>
            </nav>



            <div class="p-3 border-t text-sm">
                <a href="/" class="block rounded px-3 py-2 hover:bg-gray-100">Ke situs utama</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left rounded px-3 py-2 text-red-600 hover:bg-red-50">
                        Logout
                    </button>
                </form>
            </div>
        </aside>

        {{-- Konten --}}
        <div class="flex-1 flex flex-col">
            <header class="bg-white border-b px-6 py-3 flex items-center justify-between">
                <h1 class="text-lg font-semibold">@yield('heading', 'Dashboard')</h1>
                <span class="text-sm text-gray-600">Halo, {{ auth()->user()->name }}</span>
            </header>

            <main class="flex-1 p-6">
                @if (session('status'))
                    <div class="mb-4 rounded bg-green-100 px-4 py-2 text-sm text-green-800">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 rounded bg-red-100 px-4 py-2 text-sm text-red-800">
                         {{ session('error') }}
                   </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>