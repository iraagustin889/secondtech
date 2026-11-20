@extends('layouts.app')

@section('content')
<div class="mx-auto mt-10 max-w-md rounded-xl bg-white p-8 shadow">
    <h1 class="text-2xl font-bold">Lupa Password</h1>
    <p class="mt-2 text-sm text-slate-600">Masukkan email akunmu, kami kirim tautan untuk membuat password baru.</p>

    @if (session('status'))
        <div class="mt-4 rounded-lg bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf
        <div>
            <label class="font-semibold">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="mt-1 w-full rounded-lg border px-4 py-3">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <button class="w-full rounded-lg bg-[#1257d6] py-3 font-semibold text-white">Kirim Tautan Reset</button>
    </form>

    <p class="mt-4 text-center text-sm"><a href="{{ route('login') }}" class="text-blue-600">Kembali ke login</a></p>
</div>
@endsection
