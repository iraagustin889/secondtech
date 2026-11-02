<?php

namespace App\Policies;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ShippingMethod;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->isAdmin()
            || $order->buyer_id === $user->id
            || $this->isSeller($user, $order);
    }

    public function cancel(User $user, Order $order): bool
    {
        return $order->buyer_id === $user->id
            && in_array($order->status, [OrderStatus::PendingPayment, OrderStatus::CodRequested], true);
    }

    public function submitPayment(User $user, Order $order): bool
    {
        return $order->buyer_id === $user->id
            && $order->status === OrderStatus::PendingPayment
            && $order->payment?->status === PaymentStatus::Pending;
    }

    public function verifyPayment(User $user, Order $order): bool
    {
        return $user->isAdmin()
            && $order->status === OrderStatus::PendingPayment
            && $order->payment?->status === PaymentStatus::Submitted;
    }

    public function processFulfillment(User $user, Order $order): bool
    {
        return $this->isSeller($user, $order) && $order->status === OrderStatus::Paid;
    }

    public function ship(User $user, Order $order): bool
    {
        return $this->isSeller($user, $order)
            && $order->status === OrderStatus::Processing
            && $order->shipping_method === ShippingMethod::Expedition;
    }

    public function agreeCod(User $user, Order $order): bool
    {
        return $this->isSeller($user, $order)
            && $order->status === OrderStatus::CodRequested;
    }

    public function confirmCod(User $user, Order $order): bool
    {
        return ($order->buyer_id === $user->id || $this->isSeller($user, $order))
            && $order->status === OrderStatus::CodAgreed;
    }

    private function isSeller(User $user, Order $order): bool
    {
        return $order->orderItems()
            ->whereHas('product', fn ($query) => $query->where('user_id', $user->id))
            ->exists();
    }
}
