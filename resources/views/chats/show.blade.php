@extends('layouts.app')

@section('title', 'Chat '.$conversation->product->name.' - SECONDTECH')

@section('content')
    @php($counterpart = $conversation->buyer_id === auth()->id() ? $conversation->seller : $conversation->buyer)
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('chats.index') }}" class="text-sm text-blue-700 hover:underline">&larr; Semua pesan</a>
        <section class="mt-4 overflow-hidden rounded-xl border bg-white">
            <a href="{{ route('products.show', $conversation->product) }}" class="flex gap-4 border-b p-4 hover:bg-gray-50">
                @if ($conversation->product->image)
                    <img src="{{ asset('storage/'.$conversation->product->image) }}" alt="{{ $conversation->product->name }}" class="h-16 w-16 rounded-lg object-cover">
                @else
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400">Produk</div>
                @endif
                <div><p class="font-semibold">{{ $conversation->product->name }}</p><p class="mt-1 font-bold text-blue-700">Rp {{ number_format($conversation->product->price, 0, ',', '.') }}</p><p class="mt-1 text-sm text-gray-500">Chat dengan {{ $counterpart->name }}</p></div>
            </a>
            <div class="space-y-3 bg-gray-50 p-4">
                @forelse ($conversation->messages as $message)
                    <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}"><div class="max-w-[80%] rounded-2xl px-4 py-2 {{ $message->sender_id === auth()->id() ? 'bg-blue-600 text-white' : 'bg-white text-gray-800' }}"><p class="whitespace-pre-line text-sm">{{ $message->body }}</p><p class="mt-1 text-right text-xs {{ $message->sender_id === auth()->id() ? 'text-blue-100' : 'text-gray-400' }}">{{ $message->created_at->format('H:i') }}</p></div></div>
                @empty
                    <p class="py-10 text-center text-sm text-gray-500">Belum ada pesan. Sapa {{ $counterpart->name }} untuk mulai berdiskusi.</p>
                @endforelse
            </div>
            <form method="POST" action="{{ route('chats.messages.store', $conversation) }}" class="border-t p-4">
                @csrf
                <label for="body" class="sr-only">Pesan</label>
                <div class="flex gap-3"><textarea id="body" name="body" rows="2" maxlength="1000" required class="min-w-0 flex-1 rounded-lg border px-3 py-2" placeholder="Tulis pesan..."></textarea><button class="self-end rounded-lg bg-blue-600 px-4 py-2 font-medium text-white hover:bg-blue-700">Kirim</button></div>
                @error('body')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </form>
        </section>
    </div>
@endsection
