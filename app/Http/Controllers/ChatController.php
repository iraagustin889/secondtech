<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $conversations = Conversation::query()
            ->where(fn ($query) => $query
                ->where('buyer_id', $request->user()->id)
                ->orWhere('seller_id', $request->user()->id))
            ->with(['buyer', 'seller', 'product', 'latestMessage.sender'])
            ->latest()
            ->paginate(20);

        return view('chats.index', compact('conversations'));
    }

    public function startFromProduct(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->isPurchasable(), 404);
        abort_if($product->user_id === $request->user()->id, 403, 'Anda tidak dapat mengirim pesan ke produk milik sendiri.');

        $conversation = Conversation::query()->firstOrCreate([
            'buyer_id' => $request->user()->id,
            'seller_id' => $product->user_id,
            'product_id' => $product->id,
        ]);

        return to_route('chats.show', $conversation);
    }

    public function startFromOrder(Request $request, Order $order): RedirectResponse
    {
        Gate::authorize('view', $order);

        $product = $order->orderItems()->with('product')->firstOrFail()->product;
        abort_if($product === null, 404);

        $conversation = Conversation::query()->firstOrCreate([
            'buyer_id' => $order->buyer_id,
            'seller_id' => $product->user_id,
            'product_id' => $product->id,
        ], [
            'order_id' => $order->id,
        ]);

        if ($conversation->order_id === null) {
            $conversation->update(['order_id' => $order->id]);
        }

        return to_route('chats.show', $conversation);
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $this->authorizeParticipant($request, $conversation);

        $conversation->load(['buyer', 'seller', 'product', 'messages.sender']);

        return view('chats.show', compact('conversation'));
    }

    public function store(Request $request, Conversation $conversation): RedirectResponse
    {
        $this->authorizeParticipant($request, $conversation);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $data['body'],
        ]);
        $conversation->touch();

        return to_route('chats.show', $conversation);
    }

    private function authorizeParticipant(Request $request, Conversation $conversation): void
    {
        abort_unless(in_array($request->user()->id, [$conversation->buyer_id, $conversation->seller_id], true), 403);
    }
}
