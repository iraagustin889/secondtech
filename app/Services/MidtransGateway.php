<?php

namespace App\Services;

use App\Exceptions\PaymentGatewayException;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MidtransGateway
{
    /**
     * @return array{order_id: string, token: string, redirect_url: string}
     */
    public function createSnapTransaction(Order $order): array
    {
        $serverKey = (string) config('services.midtrans.server_key');

        if (blank($serverKey)) {
            throw new PaymentGatewayException('Midtrans belum dikonfigurasi. Isi MIDTRANS_SERVER_KEY di file .env.');
        }

        $order->loadMissing(['buyer', 'orderItems.product']);
        $gatewayOrderId = 'ST-'.$order->id.'-'.Str::upper(Str::random(10));

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(15)
                ->post($this->snapUrl().'/snap/v1/transactions', [
                    'transaction_details' => [
                        'order_id' => $gatewayOrderId,
                        'gross_amount' => (int) $order->total,
                    ],
                    'item_details' => $this->itemDetails($order),
                    'customer_details' => [
                        'first_name' => $order->recipient_name,
                        'email' => $order->buyer->email,
                        'phone' => $order->recipient_phone,
                        'billing_address' => [
                            'first_name' => $order->recipient_name,
                            'phone' => $order->recipient_phone,
                            'address' => $order->recipient_address,
                            'city' => $order->recipient_location,
                            'country_code' => 'IDN',
                        ],
                    ],
                    // QRIS Universal adalah satu-satunya metode digital di checkout.
                    'enabled_payments' => ['qris'],
                    'callbacks' => [
                        'finish' => route('orders.show', $order),
                    ],
                ]);
        } catch (ConnectionException) {
            throw new PaymentGatewayException;
        }

        $payload = $response->json();

        if (! $response->successful()
            || ! is_array($payload)
            || blank($payload['token'] ?? null)
            || blank($payload['redirect_url'] ?? null)) {
            throw new PaymentGatewayException((string) data_get($payload, 'error_messages.0', 'Midtrans tidak dapat membuat pembayaran. Silakan coba lagi.'));
        }

        return [
            'order_id' => $gatewayOrderId,
            'token' => (string) $payload['token'],
            'redirect_url' => (string) $payload['redirect_url'],
        ];
    }

    public function isConfigured(): bool
    {
        return filled(config('services.midtrans.server_key'));
    }

    /**
     * @return array<string, mixed>
     */
    public function refund(Payment $payment, int $amount, string $reason, string $refundKey): array
    {
        $serverKey = (string) config('services.midtrans.server_key');
        $transactionId = $payment->gateway_transaction_id ?: $payment->gateway_order_id;

        if ($payment->gateway !== 'midtrans' || blank($serverKey) || blank($transactionId)) {
            throw new PaymentGatewayException('Data transaksi Midtrans untuk pengembalian tidak lengkap.');
        }

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(15)
                ->post($this->apiUrl().'/v2/'.rawurlencode($transactionId).'/refund', [
                    'refund_key' => $refundKey,
                    'amount' => $amount,
                    'reason' => Str::limit($reason, 255, ''),
                ]);
        } catch (ConnectionException) {
            throw new PaymentGatewayException('Midtrans belum dapat memproses pengembalian dana. Silakan coba lagi.');
        }

        $payload = $response->json();

        if (! $response->successful() || ! is_array($payload) || (string) ($payload['status_code'] ?? '') !== '200') {
            throw new PaymentGatewayException((string) data_get($payload, 'status_message', 'Pengembalian Midtrans belum dapat diproses.'));
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function hasValidSignature(array $payload): bool
    {
        $signature = (string) ($payload['signature_key'] ?? '');
        $serverKey = (string) config('services.midtrans.server_key');

        if (blank($signature) || blank($serverKey)) {
            return false;
        }

        $expected = hash('sha512', implode('', [
            (string) ($payload['order_id'] ?? ''),
            (string) ($payload['status_code'] ?? ''),
            (string) ($payload['gross_amount'] ?? ''),
            $serverKey,
        ]));

        return hash_equals($expected, $signature);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function isSuccessful(array $payload): bool
    {
        $status = (string) ($payload['transaction_status'] ?? '');

        return $status === 'settlement'
            || ($status === 'capture' && ($payload['fraud_status'] ?? 'accept') === 'accept');
    }

    /**
     * @return array<int, array{id: string, price: int, quantity: int, name: string}>
     */
    private function itemDetails(Order $order): array
    {
        $items = $order->orderItems->map(fn ($item): array => [
            'id' => 'product-'.$item->product_id,
            'price' => (int) $item->price_snapshot,
            'quantity' => $item->qty,
            'name' => Str::limit((string) ($item->product?->name ?? 'Produk SecondTech'), 50, ''),
        ])->all();

        if ((int) $order->shipping_cost > 0) {
            $items[] = [
                'id' => 'shipping-'.$order->id,
                'price' => (int) $order->shipping_cost,
                'quantity' => 1,
                'name' => 'Ongkos kirim',
            ];
        }

        if ((int) $order->service_fee > 0) {
            $items[] = [
                'id' => 'service-fee-'.$order->id,
                'price' => (int) $order->service_fee,
                'quantity' => 1,
                'name' => 'Biaya layanan',
            ];
        }

        return $items;
    }

    private function snapUrl(): string
    {
        return config('services.midtrans.is_production')
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';
    }

    private function apiUrl(): string
    {
        return config('services.midtrans.is_production')
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }
}
