@extends('layouts.app')

@section('title', 'Pesan - SECONDTECH')

@section('content')
    <div class="mb-6"><h1 class="text-2xl font-bold">Pesan</h1><p class="mt-1 text-gray-600">Diskusikan produk dan pesanan langsung dengan lawan transaksi.</p></div>

    @if ($conversations->isEmpty())
        <div class="rounded-xl border border-dashed bg-white p-10 text-center text-gray-600">Belum ada percakapan. Buka produk lalu tekan <strong>Chat penjual</strong> untuk memulai.</div>
    @else
        <div class="space-y-3">
            @foreach ($conversations as $conversation)
                @php($counterpart = $conversation->buyer_id === auth()->id() ? $conversation->seller : $conversation->buyer)
                <a href="{{ route('chats.show', $conversation) }}" class="flex gap-4 rounded-xl border bg-white p-4 transition hover:border-blue-400 hover:shadow-sm">
                    @if ($conversation->product->image)
                        <img src="{{ asset('storage/'.$conversation->product->image) }}" alt="{{ $conversation->product->name }}" class="h-16 w-16 rounded-lg object-cover">
                    @else
                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-xs text-gray-400">Produk</div>
                    @endif
                    <div class="min-w-0 flex-1"><div class="flex items-start justify-between gap-4"><p class="font-semibold">{{ $counterpart->name }}</p><p class="shrink-0 text-xs text-gray-400">{{ $conversation->updated_at->diffForHumans() }}</p></div><p class="mt-1 truncate text-sm text-gray-700">{{ $conversation->product->name }}</p><p class="mt-1 truncate text-sm text-gray-500">{{ $conversation->latestMessage?->body ?? 'Mulai percakapan tentang produk ini.' }}</p></div>
                </a>
            @endforeach
        </div>
        <div class="mt-6">{{ $conversations->links() }}</div>
    @endif
@endsection
