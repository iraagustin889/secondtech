@extends('layouts.admin')

@section('title', 'Log Aktivitas')
@section('heading', 'Riwayat Log Aktivitas Admin')

@section('content')
    <div class="overflow-x-auto rounded-lg border bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-3">Waktu</th>
                    <th class="px-4 py-3">Admin</th>
                    <th class="px-4 py-3">Aksi</th>
                    <th class="px-4 py-3">Target</th>
                    <th class="px-4 py-3">Alasan</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($logs as $log)
                    <tr>
                        <td class="px-4 py-3">{{ $log->created_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $log->admin->name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $log->action }}</td>
                        <td class="px-4 py-3">{{ $log->target_type }} #{{ $log->target_id }}</td>
                        <td class="px-4 py-3">{{ $log->reason ?: '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada aktivitas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
@endsection