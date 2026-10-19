@extends('layouts.app')

@section('title', 'Daftar - SECONDTECH')

@section('content')
<div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow">
    <h1 class="text-2xl font-bold mb-6">Daftar Akun</h1>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium mb-1">Nama</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full border rounded px-3 py-2">
            @error('name') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full border rounded px-3 py-2">
            @error('email') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Password</label>
            <input type="password" name="password" required
                   class="w-full border rounded px-3 py-2">
            @error('password') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Konfirmasi Password</label>
            <input type="password" name="password_confirmation" required
                   class="w-full border rounded px-3 py-2">
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
            Daftar
        </button>
    </form>

    <p class="text-sm mt-4 text-center">
        Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Login</a>
    </p>
</div>
@endsection
