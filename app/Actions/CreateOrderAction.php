<?php

namespace App\Actions;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Enums\ShippingStatus;
use App\Exceptions\OrderUnavailableException;
use App\Models\Order;
use App\Models\Product;
use App\Models\ServiceFeeConfiguration;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    /**
     * @param  array{recipient_name: string, recipient_phone: string, recipient_address: string, recipient_location: string, quantity: int, payment_method: 'cod'|'qris'}  $checkout
     */
    public function handle(User $buyer, Product $product, array $checkout): Order
    {
        return DB::transaction(function () use ($buyer, $product, $checkout): Order {
            $lockedProduct = Product::query()
                ->lockForUpdate()
                ->findOrFail($product->id);

            if (! $lockedProduct->isPurchasable()) {
                throw new OrderUnavailableException;
            }

            if ($lockedProduct->user_id === $buyer->id) {
                throw new OrderUnavailableException('Anda tidak dapat membeli produk milik sendiri.');
            }

            $quantity = (int) $checkout['quantity'];

            if ($quantity > $lockedProduct->stock) {
                throw new OrderUnavailableException('Jumlah pesanan melebihi stok yang tersedia.');
            }

            // Semua transaksi baru adalah temu langsung. Nilai `cod` pada
            // shipping dipertahankan agar pesanan lama tetap kompatibel.
            $shippingMethod = ShippingMethod::Cod;
            $paymentMethod = $checkout['payment_method'];
            $isCod = $paymentMethod === 'cod';
            $shippingCost = 0;

            $subtotal = (int) $lockedProduct->price * $quantity;
            // Uang COD berpindah langsung dari pembeli ke penjual, maka tidak
            // ada biaya platform yang tidak dapat ditagih otomatis. Biaya
            // layanan hanya diberlakukan pada QRIS/rekber.
            $serviceFee = $isCod ? 0 : ServiceFeeConfiguration::calculate($subtotal);

            $order = Order::create([
                'buyer_id' => $buyer->id,
                'subtotal' => $subtotal,
                'service_fee' => $serviceFee,
                'shipping_cost' => $shippingCost,
                'total' => $subtotal + $serviceFee + $shippingCost,
                'status' => $isCod
                    ? OrderStatus::CodRequested
                    : OrderStatus::PendingPayment,
                'shipping_method' => $shippingMethod,
                'recipient_name' => $checkout['recipient_name'],
                'recipient_phone' => $checkout['recipient_phone'],
                'recipient_address' => $checkout['recipient_address'],
                'recipient_location' => $checkout['recipient_location'],
            ]);

            $order->update([
                'order_number' => sprintf('ST-%s-%06d', now()->format('Ymd'), $order->id),
            ]);

            $order->orderItems()->create([
                'product_id' => $lockedProduct->id,
                'price_snapshot' => $lockedProduct->price,
                'qty' => $quantity,
            ]);

            $order->payment()->create([
                'method' => $paymentMethod,
                'status' => PaymentStatus::Pending,
            ]);

            $order->shipping()->create([
                'method' => $shippingMethod,
                'status' => $isCod
                    ? ShippingStatus::CodRequested
                    : ShippingStatus::Selected,
                'origin_id' => $lockedProduct->shipping_origin_id,
                'weight' => $lockedProduct->weight * $quantity,
                'cost' => $shippingCost,
            ]);

            $newStock = $lockedProduct->stock - $quantity;

            $lockedProduct->update([
                'stock' => $newStock,
                'status' => $newStock > 0 ? 'active' : 'reserved',
            ]);

            $completedOrder = $order->fresh(['buyer', 'orderItems.product', 'payment', 'shipping']);
            User::query()->findOrFail($lockedProduct->user_id)->notify(new NewOrderNotification($completedOrder));

            return $completedOrder;
        }, attempts: 3);
    }
}
