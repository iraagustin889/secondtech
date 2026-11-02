@extends('layouts.app')

@section('title', 'Edit Profil - SECONDTECH')

@section('content')
    <h1 class="text-2xl font-bold mb-6">Edit Profil</h1>

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 text-red-700 text-sm rounded border border-red-200">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('profile.update') }}"
          class="bg-white border rounded p-6 max-w-xl grid gap-4">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm text-gray-600 mb-1">Nama</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                   class="w-full border rounded px-3 py-2 text-sm">
        </div>

        <div>
            <label for="email" class="block text-sm text-gray-600 mb-1">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                   class="w-full border rounded px-3 py-2 text-sm">
        </div>

        <div>
            <label for="phone" class="block text-sm text-gray-600 mb-1">Nomor telepon</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                   placeholder="Contoh: 081234567890"
                   class="w-full border rounded px-3 py-2 text-sm">
        </div>

        <div>
            <label for="address" class="block text-sm text-gray-600 mb-1">Alamat</label>
            <textarea id="address" name="address" rows="3"
                      class="w-full border rounded px-3 py-2 text-sm">{{ old('address', $user->address) }}</textarea>
        </div>

        <div>
            <label for="location" class="block text-sm text-gray-600 mb-1">Kota/lokasi</label>
            <input type="text" id="location" name="location" value="{{ old('location', $user->location) }}"
                   placeholder="Contoh: Banyuwangi"
                   class="w-full border rounded px-3 py-2 text-sm">
        </div>

        <div>
            <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded text-sm hover:bg-blue-700">
                Simpan
            </button>
        </div>
    </form>
@endsection
