@extends('layouts.app')

@section('content')
<div class="mx-auto mt-10 max-w-md rounded-xl bg-white p-8 shadow">
    <h1 class="text-2xl font-bold">Buat Password Baru</h1>

    <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div>
            <label class="font-semibold">Email</label>
            <input type="email" name="email" value="{{ old('email', $email) }}" required
                   class="mt-1 w-full rounded-lg border px-4 py-3">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="font-semibold">Password baru</label>
            <input type="password" name="password" required class="mt-1 w-full rounded-lg border px-4 py-3">
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="font-semibold">Ulangi password baru</label>
            <input type="password" name="password_confirmation" required class="mt-1 w-full rounded-lg border px-4 py-3">
        </div>
        <button class="w-full rounded-lg bg-[#1257d6] py-3 font-semibold text-white">Simpan Password</button>
    </form>
</div>
@endsection
