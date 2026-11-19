@extends('layouts.app')

@section('title', 'Pesan')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Pesan</h1>
        <p class="mt-1 text-gray-600">Diskusikan produk dan pesanan langsung dengan lawan transaksi Anda.</p>
    </div>

    @forelse ($conversations as $conversation)
        @php
            $otherParticipant = $conversation->buyer_id === auth()->id() ? $conversation->seller : $conversation->buyer;
            $latestMessage = $conversation->latestMessage;
        @endphp
        <a href="{{ route('chats.show', $conversation) }}" class="mb-3 block rounded-xl border bg-white p-4 transition hover:border-blue-400 hover:shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="font-semibold">{{ $otherParticipant->name }}</p>
                    <p class="mt-1 truncate text-sm text-gray-600">{{ $conversation->product->name }}</p>
                    <p class="mt-2 truncate text-sm text-gray-500">{{ $latestMessage ? $latestMessage->sender->name.': '.$latestMessage->body : 'Belum ada pesan. Mulai percakapan sekarang.' }}</p>
                </div>
                <span class="shrink-0 text-xs text-gray-400">{{ $latestMessage?->created_at?->format('d M H:i') ?? $conversation->created_at->format('d M H:i') }}</span>
            </div>
        </a>
    @empty
        <div class="rounded-xl border border-dashed bg-white p-10 text-center text-gray-600">
            Belum ada percakapan. Buka detail produk lalu klik <strong>Chat penjual</strong> untuk memulai.
        </div>
    @endforelse

    <div class="mt-6">
        {{ $conversations->links() }}
    </div>
@endsection
