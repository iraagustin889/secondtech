<?php

namespace App\Actions;

use App\Models\ServiceFeeConfiguration;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Enums\ShippingStatus;
use App\Exceptions\OrderUnavailableException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    /**
     * @param  array{recipient_name: string, recipient_phone: string, recipient_address: string, recipient_location: string, shipping_method: string}  $checkout
     * @param  array{courier?: string, service?: string, cost?: int, estimate?: string, origin_id?: int, destination_id?: int, destination_name?: string}  $shippingQuote
     */
    public function handle(User $buyer, Product $product, array $checkout, array $shippingQuote = []): Order
    {
        return DB::transaction(function () use ($buyer, $product, $checkout, $shippingQuote): Order {
            $lockedProduct = Product::query()
                ->lockForUpdate()
                ->findOrFail($product->id);

            if (! $lockedProduct->isPurchasable()) {
                throw new OrderUnavailableException;
            }

            $shippingMethod = ShippingMethod::from($checkout['shipping_method']);
            $shippingCost = $shippingMethod === ShippingMethod::Expedition
                ? (int) ($shippingQuote['cost'] ?? 0)
                : 0;

            if ($shippingMethod === ShippingMethod::Expedition && $shippingCost < 1) {
                throw new OrderUnavailableException('Pilihan ongkos kirim tidak lagi tersedia. Silakan cek ulang ongkir.');
            }

            $subtotal = (int) $lockedProduct->price;
            $serviceFee = ServiceFeeConfiguration::calculate((int) $subtotal);

            $order = Order::create([
                'buyer_id' => $buyer->id,
                'subtotal' => $subtotal,
                'service_fee' => $serviceFee,
                'shipping_cost' => $shippingCost,
                'total' => $subtotal + $serviceFee + $shippingCost,
                'status' => $shippingMethod === ShippingMethod::Cod
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
                'price_snapshot' => $subtotal,
                'qty' => 1,
            ]);

            $order->payment()->create([
                'method' => $shippingMethod === ShippingMethod::Cod ? 'cod' : 'transfer_bank',
                'status' => PaymentStatus::Pending,
            ]);

            $order->shipping()->create([
                'method' => $shippingMethod,
                'status' => $shippingMethod === ShippingMethod::Cod
                    ? ShippingStatus::CodRequested
                    : ShippingStatus::Selected,
                'courier' => $shippingQuote['courier'] ?? null,
                'service' => $shippingQuote['service'] ?? null,
                'origin_id' => $shippingQuote['origin_id'] ?? $lockedProduct->shipping_origin_id,
                'destination_id' => $shippingQuote['destination_id'] ?? null,
                'destination_name' => $shippingQuote['destination_name'] ?? null,
                'weight' => $lockedProduct->weight,
                'cost' => $shippingCost,
                'estimated_delivery' => $shippingQuote['estimate'] ?? null,
            ]);

            $lockedProduct->update([
                'stock' => $lockedProduct->stock - 1,
                'status' => 'reserved',
            ]);

            return $order->fresh(['orderItems.product', 'payment', 'shipping']);
        }, attempts: 3);
    }
}
