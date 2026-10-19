@extends('layouts.admin')

@section('title', 'Pengguna')
@section('heading', 'Daftar Pengguna')

@section('content')
    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-4 flex gap-2">
        <input type="text" name="search" value="{{ $search }}"
               placeholder="Cari nama atau email..."
               class="w-full max-w-sm rounded border px-3 py-2 text-sm">
        <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm text-white hover:bg-blue-700">
            Cari
        </button>
        @if ($search)
            <a href="{{ route('admin.users.index') }}" class="rounded border px-4 py-2 text-sm hover:bg-gray-50">Reset</a>
        @endif
    </form>

    <div class="overflow-x-auto rounded-lg border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-4 py-3">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3 capitalize">{{ $user->role }}</td>
                        <td class="px-4 py-3">
                            @if ($user->is_active)
                                <span class="rounded bg-green-100 px-2 py-1 text-xs text-green-700">Aktif</span>
                            @else
                                <span class="rounded bg-red-100 px-2 py-1 text-xs text-red-700">Nonaktif</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">Pengguna tidak ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endsection