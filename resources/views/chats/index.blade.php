@extends('layouts.app')

@section('title', 'Pesan')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Pesan</h1>
        <p class="mt-1 text-gray-600">Diskusikan produk, jadwal pertemuan, dan pesanan langsung dengan lawan transaksi Anda.</p>
    </div>

    @forelse ($conversations as $conversation)
        @php
            $otherParticipant = $conversation->buyer_id === auth()->id() ? $conversation->seller : $conversation->buyer;
            $latestMessage = $conversation->latestMessage;
        @endphp
        <a href="{{ route('chats.show', $conversation) }}" class="mb-3 block rounded-xl border bg-white p-4 transition hover:border-blue-400 hover:shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div class="flex min-w-0 gap-3">
                    @if ($conversation->product->image)
                        <img src="{{ asset('storage/'.$conversation->product->image) }}" alt="" class="h-12 w-12 shrink-0 rounded-lg object-cover">
                    @else
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xl">💬</div>
                    @endif
                    <div class="min-w-0">
                    <p class="font-semibold">{{ $otherParticipant->name }}</p>
                    <p class="mt-1 truncate text-sm text-gray-600">{{ $conversation->product->name }}</p>
                    <p class="mt-2 truncate text-sm text-gray-500">{{ $latestMessage ? $latestMessage->sender->name.': '.$latestMessage->body : 'Belum ada pesan. Mulai percakapan sekarang.' }}</p>
                    </div>
                </div>
                <div class="shrink-0 text-right"><span class="text-xs text-gray-400">{{ $latestMessage?->created_at?->format('d M H:i') ?? $conversation->created_at->format('d M H:i') }}</span>@if ($conversation->unread_messages_count)<span class="mt-2 inline-flex min-w-5 items-center justify-center rounded-full bg-[#1257d6] px-1.5 py-0.5 text-[10px] font-bold text-white">{{ $conversation->unread_messages_count }}</span>@endif</div>
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
