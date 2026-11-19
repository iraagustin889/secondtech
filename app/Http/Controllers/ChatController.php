<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Models\Conversation;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Conversation::class);

        $conversations = Conversation::query()
            ->where(fn ($query) => $query
                ->where('buyer_id', $request->user()->id)
                ->orWhere('seller_id', $request->user()->id))
            ->with(['buyer', 'seller', 'product', 'latestMessage.sender'])
            ->latest()
            ->paginate(15);

        return view('chats.index', compact('conversations'));
    }

    public function startFromProduct(Request $request, Product $product): RedirectResponse
    {
        abort_if($product->user_id === $request->user()->id, 422, 'Anda tidak dapat mengirim pesan ke produk sendiri.');
        abort_unless($product->status === 'active', 404);

        $conversation = $this->conversationFor($request->user(), $product);

        return to_route('chats.show', $conversation);
    }

    public function startFromOrder(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('view', $order);

        $item = $order->orderItems()
            ->with('product')
            ->when(
                $order->buyer_id !== $request->user()->id,
                fn ($query) => $query->whereHas('product', fn ($productQuery) => $productQuery->where('user_id', $request->user()->id)),
            )
            ->firstOrFail();
        $product = $item->product;
        abort_unless($product instanceof Product, 404);

        $conversation = Conversation::firstOrCreate([
            'buyer_id' => $order->buyer_id,
            'seller_id' => $product->user_id,
            'product_id' => $product->id,
        ]);

        return to_route('chats.show', $conversation);
    }

    public function show(Request $request, Conversation $conversation): View
    {
        Gate::authorize('view', $conversation);

        $conversation->messages()
            ->where('sender_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->load(['buyer', 'seller', 'product', 'messages.sender']);

        return view('chats.show', compact('conversation'));
    }

    public function store(StoreMessageRequest $request, Conversation $conversation): RedirectResponse
    {
        Gate::authorize('send', $conversation);

        $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $request->validated('body'),
        ]);
        $conversation->touch();

        return to_route('chats.show', $conversation);
    }

    private function conversationFor(User $buyer, Product $product): Conversation
    {
        return Conversation::firstOrCreate([
            'buyer_id' => $buyer->id,
            'seller_id' => $product->user_id,
            'product_id' => $product->id,
        ]);
    }
}
