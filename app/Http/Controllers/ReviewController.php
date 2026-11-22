<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Order $order): RedirectResponse
    {
        // Hanya pembeli pemilik pesanan
        abort_unless($order->buyer_id === $request->user()->id, 403);

        // Hanya setelah pesanan selesai
        abort_unless($order->status === OrderStatus::Completed, 422, 'Ulasan hanya bisa diberikan setelah pesanan selesai.');

        // Satu ulasan per pesanan
        if ($order->review()->exists()) {
            return back()->with('error', 'Pesanan ini sudah diberi ulasan.');
        }

        // (1) Validasi: tambah aturan 'photo'
        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'rating.required' => 'Pilih rating 1 sampai 5.',
            'rating.between' => 'Rating harus 1 sampai 5.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $item = $order->orderItems()->with('product')->firstOrFail();

        // (2) Simpan file foto (kalau ada) ke storage/app/public/reviews
        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('reviews', 'public')
            : null;

        Review::create([
            'order_id' => $order->id,
            'product_id' => $item->product_id,
            'buyer_id' => $order->buyer_id,
            'seller_id' => $item->product->user_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'photo' => $photoPath, // (3) simpan path foto
        ]);

        return to_route('orders.show', $order)->with('success', 'Terima kasih atas ulasan Anda.');
    }
}