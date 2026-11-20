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

    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
          class="bg-white border rounded p-6 max-w-xl grid gap-4">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm text-gray-600 mb-2">Foto profil</label>
            <div class="flex items-center gap-4">
                @if ($user->avatarUrl())
                    <img src="{{ $user->avatarUrl() }}" alt="Foto profil"
                         class="w-16 h-16 rounded-full object-cover border">
                @else
                    <div class="w-16 h-16 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xl font-bold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <input type="file" id="avatar" name="avatar" accept="image/png,image/jpeg,image/webp"
                           class="text-sm">
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, atau WEBP. Maksimal 2 MB.</p>
                    @if ($user->avatar)
                        <label class="inline-flex items-center gap-2 text-xs text-gray-600 mt-2">
                            <input type="checkbox" name="remove_avatar" value="1"> Hapus foto
                        </label>
                    @endif
                </div>
            </div>
        </div>

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

        <div class="border-t pt-4">
            <p class="mb-3 font-medium text-gray-800">Profil penjual</p>
            <label for="seller_bio" class="block text-sm text-gray-600 mb-1">Tentang toko / penjual</label>
            <textarea id="seller_bio" name="seller_bio" rows="4" maxlength="1000"
                      placeholder="Ceritakan jenis produk, kondisi barang, atau cara Anda melayani pembeli."
                      class="w-full border rounded px-3 py-2 text-sm">{{ old('seller_bio', $user->seller_bio) }}</textarea>
            <p class="mt-1 text-xs text-gray-500">Profil ini terlihat pada halaman penjual. Satu akun dapat membeli sekaligus menjual produk.</p>
        </div>

        <div>
            <button type="submit" class="bg-blue-600 text-white px-5 py-2 rounded text-sm hover:bg-blue-700">
                Simpan
            </button>
        </div>
    </form>
@endsection
