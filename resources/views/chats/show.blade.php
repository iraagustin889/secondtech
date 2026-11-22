@extends('layouts.app')

@section('title', 'Chat '.$conversation->product->name)

@section('content')
    @php
        $otherParticipant = $conversation->buyer_id === auth()->id() ? $conversation->seller : $conversation->buyer;
    @endphp

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('chats.index') }}" class="text-sm text-blue-700 hover:underline">&larr; Semua pesan</a>
            <h1 class="mt-2 text-2xl font-bold">{{ $otherParticipant->name }}</h1>
        </div>
    </div>

    <a href="{{ route('products.show', $conversation->product) }}" class="mb-4 flex items-center gap-3 rounded-xl border bg-white p-3 transition hover:border-blue-400">
        @if ($conversation->product->image)
            <img src="{{ asset('storage/'.$conversation->product->image) }}" alt="{{ $conversation->product->name }}" class="h-14 w-14 rounded-lg object-cover">
        @else
            <div class="flex h-14 w-14 items-center justify-center rounded-lg bg-blue-50 text-2xl">📦</div>
        @endif
        <div class="min-w-0 flex-1"><p class="truncate font-semibold text-slate-800">{{ $conversation->product->name }}</p><p class="mt-1 text-sm font-bold text-[#1257d6]">Rp {{ number_format($conversation->product->price, 0, ',', '.') }}</p><p class="mt-1 text-xs text-slate-500">Lihat detail produk</p></div>
        <span class="text-sm font-semibold text-[#1257d6]">Lihat</span>
    </a>

    <section class="rounded-xl border bg-white">
        <div class="max-h-[32rem] min-h-64 space-y-4 overflow-y-auto p-5">
            @forelse ($conversation->messages as $message)
                <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[80%] rounded-2xl px-4 py-3 {{ $message->sender_id === auth()->id() ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-800' }}">
                        <p class="text-sm whitespace-pre-line">{{ $message->body }}</p>
                        <p class="mt-1 text-right text-xs {{ $message->sender_id === auth()->id() ? 'text-blue-100' : 'text-gray-400' }}">{{ $message->created_at->format('d M Y H:i') }}</p>
                    </div>
                </div>
            @empty
                <p class="py-12 text-center text-sm text-gray-500">Belum ada pesan. Sapa {{ $otherParticipant->name }} untuk memulai percakapan.</p>
            @endforelse
        </div>

        <form method="POST" action="{{ route('chats.messages.store', $conversation) }}" class="border-t p-4">
            @csrf
            <label for="body" class="sr-only">Pesan</label>
            <div class="flex gap-3">
                <textarea id="body" name="body" rows="2" maxlength="1000" required class="min-w-0 flex-1 rounded-lg border px-3 py-2 text-sm" placeholder="Tulis pesan untuk {{ $otherParticipant->name }}...">{{ old('body') }}</textarea>
                <button class="self-end rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Kirim</button>
            </div>
            @error('body')
                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </form>
    </section>
@endsection
