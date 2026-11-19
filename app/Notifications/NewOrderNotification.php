<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Order $order) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $item = $this->order->orderItems->first();

        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'product_name' => $item?->product?->name ?? 'Produk Anda',
            'buyer_name' => $this->order->buyer?->name ?? 'Pembeli',
            'message' => 'Pesanan baru masuk dan menunggu tindakan Anda.',
        ];
    }
}
